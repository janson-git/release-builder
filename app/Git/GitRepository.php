<?php

namespace Git;

use Admin\App;
use Service\Util\Fs;
use User\Auth;

class GitRepository
{
    public const PROTOCOL_HTTPS = 'https';
    public const PROTOCOL_SSH = 'ssh';

    private string $repository;
    private string $path;
    private string $mainBranch = '';
    private string $remoteUrl;

    private Fs $fs;

    /** @var string|NULL @internal */
    private $cwd;

    private array $remoteBranches = [];

    private string $lastOutput = '';
    private ?string $sshKeyPath = '';

    private ?string $accessToken = null;

    private bool $ignoreKnownHosts = true;

    private ?string $commitAuthorName = null;
    private ?string $commitAuthorEmail = null;

    /**
     * @param $repository
     *
     * @throws GitException
     */
    public function __construct(string $repository)
    {
        if (basename($repository) === '.git') {
            $repository = dirname($repository);
        }

        $realpath = realpath($repository);
        if ($realpath === false) {
            $this->exception("Repository '$repository' not found.");
        }

        $this->path       = $repository;
        $this->repository = $realpath;

        $this->fs = new Fs();
        $this->fs->setWorkDir($this->repository);

        $user = App::i()->getAuth()->getUser();
        $userLogin = $user->getLogin();
        if ($userLogin !== Auth::USER_ANONIM) {
            $this->setSshKeyPath(SSH_KEYS_DIR . "/{$userLogin}");
        }

        $this->commitAuthorName = $user->getCommitAuthorName();
        $this->commitAuthorEmail = $user->getCommitAuthorEmail();
        $this->accessToken = $user->getAccessToken();

        $this->remoteUrl  = $this->getRemoteUrl();

        $this->setMainBranch();
    }

    /**
     * GitHub's repositories based on one of : main, master
     * We need check what of base is used in repository
     *
     * @throws GitException
     */
    private function setMainBranch(): void
    {
        $remoteBranches = $this->getRemoteBranches() ?? [];

        foreach ($remoteBranches as $branchName) {
            if (!in_array($branchName,  ['master', 'main']) && !empty($this->mainBranch)) {
                continue;
            }
            // if repo has both 'main' and 'master' - use 'main'
            if ($this->mainBranch === 'main') {
                break;
            }
            $this->mainBranch = $branchName;
        }
    }

    public function getRepositoryPath(): string
    {
        return $this->repository;
    }
    
    /**
     * Creates a tag.
     * `git tag <name>`
     *
     * @throws GitException
     */
    public function createTag(string $name): self
    {
        return $this->begin()->run('git tag', $name)->end();
    }
    
    /**
     * Removes tag.
     * `git tag -d <name>`
     *
     * @throws GitException
     */
    public function removeTag(string $name): self
    {
        return $this->begin()->run('git tag', ['-d' => $name])->end();
    }
    
    /**
     * Renames tag.
     * `git tag <new> <old>`
     * `git tag -d <old>`
     *
     * @throws GitException
     */
    public function renameTag(string $oldName, string $newName): self
    {
        return $this->begin()// http://stackoverflow.com/a/1873932
        // create new as alias to old (`git tag NEW OLD`)
        ->run('git tag', $newName, $oldName)// delete old (`git tag -d OLD`)
        ->removeTag($oldName)// WARN! removeTag() calls end() method!!!
        ->end();
    }
    
    /**
     * Returns list of tags in repo.
     *
     * @return string[]|NULL  NULL => no tags
     * @throws GitException
     */
    public function getTags(): ?array
    {
        return $this->extractFromCommand('git tag', 'trim');
    }
    
    /**
     * Merges branches.
     * `git merge <options> <name>`
     *
     * @throws GitException
     */
    public function merge(string $branch, ?array $options = null): self
    {
        return $this->begin()->run('git merge ', $options, $branch)->end();
    }
    
    /**
     * Creates new branch.
     * `git branch <name>`
     * (optionaly) `git checkout <name>`
     *
     * @throws GitException
     */
    public function createBranch(string $name, bool $checkout = false): self
    {
        $this->begin();
        
        // git branch $name
        $this->run('git branch', $name);
        
        if ($checkout) {
            $this->checkout($name);
        }
        
        return $this->end();
    }
    
    /**
     * Removes branch.
     * `git branch -d <name>`
     *
     * @throws GitException
     */
    public function removeBranch(string $name): self
    {
        return $this->begin()->run('git branch', array(
            '-D' => $name,
        ))->end();
    }
    
    /**
     * Gets name of current branch
     * `git branch` + magic
     *
     * @throws GitException
     */
    public function getCurrentBranchName(): string
    {
        try {
            $branch = $this->extractFromCommand('git branch -a', function ($value) {
                if (isset($value[0]) && $value[0] === '*') {
                    return trim(substr($value, 1));
                }
                
                return false;
            });
            
            if (is_array($branch)) {
                return $branch[0];
            }
        } catch (GitException $e) {
        }
        $this->exception('Getting current branch name failed.');
    }
    
    /**
     * Returns list of local branches in repo.
     *
     * @return string[]|NULL  NULL => no branches
     * @throws GitException
     */
    public function getBranches(): ?array
    {
        return $this->extractFromCommand('git branch ', function ($value) {
            if (strrpos($value, 'HEAD detached') !== false ) {
                return false;
            }
            return trim(str_replace('*', '', $value));
        });
    }

    /**
     * Returns sorted list of local branches in repo with date, relative date and name.
     * @return array
     * @throws GitException
     */
    public function getBranchesWithDetails(): array
    {
        // get:
        //   -l -> only local branches
        //   --sort=-committerdate -> sorted by last commit date desc
        //   --format=... -> with date, relative date and branchname
        // Result:
        // 2023-11-16 00:03:12 +0300 // 29 minutes ago // master
        // 2023-11-12 23:02:55 +0300 // 3 days ago // refactor_and_clean
        $cmd = 'git branch -l --sort=-committerdate --format="%(committerdate:iso8601) // %(committerdate:relative) // %(refname:short)" | grep -v HEAD';
        $result = $this->extractFromCommand($cmd, function ($output) {
            list($date, $relativeDate, $name) = explode('//', $output);

            return [
                'date' => trim($date),
                'relativeDate' => trim($relativeDate),
                'branch' => trim($name),
            ];
        });

        $keyed = [];
        foreach ($result as $info) {
            $keyed[ $info['branch'] ] = $info;
        }

        return $keyed;
    }
    
    /**
     * Returns list of remote branches in repo.
     * @return string[]|NULL  NULL => no branches
     * @throws GitException
     */
    public function getRemoteBranches(): ?array
    {
        if (!$this->remoteBranches) {
            $this->remoteBranches = $this->extractFromCommand('git branch -r', function ($value) {
                return trim(str_replace('origin/', '', $value));
            });
        }
        
        return $this->remoteBranches;
    }

    /**
     * @return false|string
     * @throws GitException
     */
    public function mergeRemoteIfHas(string $branch)
    {
        $this->getRemoteBranches();
        
        if (in_array($branch, $this->remoteBranches)) {
            $this->merge("origin/{$branch}", ['--no-ff', '--log=50', '--stat']);
            
            return $this->lastOutput;
        }
        
//        $this->exception($this->path.' not found '. $branch .' in '.implode(', ',$this->remoteBranches).' branch');
        
        return false;
    }
    
    public function getRemotesLastChangeTime()
    {
        $cmg = "git for-each-ref --format='%(committerdate:format:%s) %(refname)' --sort -committerdate refs/remotes/";
        
        $d = $this->extractFromCommand($cmg, function ($value) {
            return explode(' ', $value, 2);
        });
        
        $result = array_column($d, 0, 1);
        arsort($result);
        
        return $result;
    }
    
    /**
     * Returns list of local branches in repo.
     * @return string[]|NULL  NULL => no branches
     * @throws GitException
     */
    public function getLocalBranches(): ?array
    {
        return $this->extractFromCommand('git branch', function ($value) {
            return trim(substr($value, 1));
        });
    }

    private function filterBranchName(string $name): string
    {
        $name = trim($name);
        $mainBranchPattern = "#^(?<origin>origin/)?(?<master>master|main)$#";

        if (preg_match($mainBranchPattern, $name, $m)) {
            $name = !empty($m['origin']) ? "origin/{$this->mainBranch}" : $this->mainBranch;
        }

        return $name;
    }

    /**
     * Checkout branch.
     * `git checkout <branch>`
     *
     * @throws GitException
     */
    public function checkout(string $name): self
    {
        $name = $this->filterBranchName($name);
        return $this->begin()->run('git checkout', $name)->end();
    }

    /**
     * @throws GitException
     */
    public function checkoutToNewBranch(string $name, string $toBranch): self
    {
        $name = $this->filterBranchName($name);
        return $this->begin()->run('git checkout ', $name, ['-b' => $toBranch])->end();
    }

    public function checkoutToMainBranch(): self
    {
        return $this->checkout($this->mainBranch);
    }

    public function checkoutToOriginMainBranch(): self
    {
        return $this->checkout("origin/{$this->mainBranch}");
    }

    public function checkoutToNewBranchFromOriginMain(string $newBranchName): self
    {
        return $this->checkoutToNewBranch("origin/{$this->mainBranch}", $newBranchName);
    }

    /**
     * @throws GitException
     */
    public function checkoutNewBranchFromDetached(string $name): self
    {
        return $this->begin()->run('git checkout -b ', $name)->end();
    }

    /**
     * @throws GitException
     */
    public function checkoutBranchOrResetAndCheckout(string $name): self
    {
        return $this->begin()->run('git checkout -B ', $name)->end();
    }

    /**
     * @throws GitException
     */
    public function getLastTag(string $prefix = '') : ?string
    {
        foreach (array_reverse((array) $this->getTags()) as $tag) {
            if ($prefix && false === strpos($tag, $prefix)) {
                continue;
            }

            return (string) $tag;
        }

        return null;
    }
    
    /**
     * Checkout branch.
     * `git checkout <branch>`
     *
     * @throws GitException
     */
    public function fullReset(): self
    {
        return $this->begin()
            ->run('git reset --hard HEAD')
            ->run('git clean -df')
            ->end();
    }
    
    /**
     * Removes file(s).
     * `git rm <file>`
     *
     * @param  string[] $files
     *
     * @throws GitException
     */
    public function removeFiles(array $files): self
    {
        $this->begin();
        
        foreach ($files as $item) {
            $this->run('git rm', $item, '-r');
        }
        
        return $this->end();
    }

    /**
     * Adds file(s).
     * `git add <file>`
     *
     * @param string[] $files
     *
     * @throws GitException
     */
    public function addFiles(array $files): self
    {
        $this->begin();
        
        foreach ($files as $item) {
            // TODO: ?? is file($repo . / . $item) ??
            $this->run('git add', $item);
        }
        
        return $this->end();
    }
    
    /**
     * Renames file(s).
     * `git mv <file>`
     *
     * @throws GitException
     */
    public function renameFile(string $file, string $to): self
    {
        return $this->begin()->run('git mv', $file, $to)->end();
    }
    
    /**
     * Commits changes
     * `git commit <params> -m <message>`
     *
     * @param string $message
     * @param  string[]|null $params As array of param => value pairs
     *
     * @throws GitException
     * @return self
     */
    public function commit(string $message, ?array $params = null): self
    {
        if (!is_array($params)) {
            $params = [];
        }
        
        return $this->begin()->run("git commit", $params, [
            '-m' => $message,
        ])->end();
    }

    /**
     * Exists changes?
     * `git status` + magic
     *
     * @return bool
     * @throw \Exception
     */
    public function hasChanges(): bool
    {
        $this->begin();
        $this->fs->exec('git status', $out, $res, __METHOD__);
        $this->end();
        $out = implode(' ', $out);
        
        $a1 = (strpos($out, 'nothing to commit')) === false; 
        $a2 = (strpos($out, 'no changes added to commit')) === false;
        $a3  = strrpos($out, 'All conflicts fixed but you are still merging') !== false;
        return ($a1 && $a2) || $a3; // FALSE => changes
    }

    private function getTokenizedRemoteUrl(?string $tokenToInject = null, ?string $remoteUrl = null): string
    {
        if ($remoteUrl === null) {
            $remoteUrl = $this->getRemoteUrl();
        }
        // split url, inject token and join result
        if ($this->parseProtocolFromUrl($remoteUrl) === self::PROTOCOL_HTTPS) {
            $pattern = '#(?<protocol>[\w]+://)(?<token>[\w]*\@)?(?<url>.*)#';

            if (preg_match($pattern, $remoteUrl, $m)) {
                $token = empty($tokenToInject) ? '' : "{$tokenToInject}@";
                $remoteUrl = $m['protocol'] . $token . $m['url'];
            }
        }
        return $remoteUrl;
    }

    private function getRemoteUrl(): string
    {
        $this->begin();
        $this->fs->exec('git remote -v', $out, $res, __METHOD__);
        $this->end();

        $line = array_shift($out);
        list(, $url, ) = preg_split('#\s+#', $line);

        return $url;
    }

    /**
     * @return string Available protocols: https, ssh
     */
    private function parseProtocolFromUrl(string $urlToParse): string
    {
        switch ($urlToParse) {
            case str_starts_with($urlToParse, 'https:'):
                return self::PROTOCOL_HTTPS;
            default:
                return self::PROTOCOL_SSH;
        }
    }

    protected function begin(): self
    {
        if ($this->cwd === null) // TODO: good idea??
        {
            $this->cwd = getcwd();
//            chdir($this->repository);
        }
        
        return $this;
    }
    
    protected function end(): self
    {
        if (is_string($this->cwd)) {
//            chdir($this->cwd);
        }
        
        $this->cwd = null;
        
        return $this;
    }

    /**
     * @throws GitException
     */
    public function fetch(): self
    {
        $tokenizedRepoUrl = $this->getTokenizedRemoteUrl($this->accessToken, $this->remoteUrl);

        return $this->begin()->run("git fetch -p {$tokenizedRepoUrl}")->end();
    }

    /**
     * Pull changes from a remote
     *
     * @throws GitException
     */
    public function pull(?array $params = null): self
    {
        if (!is_array($params)) {
            $params = [];
        }

        $tokenizedRepoUrl = $this->getTokenizedRemoteUrl($this->accessToken, $this->remoteUrl);

        return $this->begin()->run("git pull {$tokenizedRepoUrl}", $params)->end();
    }

    /**
     * @throws GitException
     */
    public function push(?array $params = null): self
    {
        if (!is_array($params)) {
            $params = [];
        }

        $tokenizedRepoUrl = $this->getTokenizedRemoteUrl($this->accessToken, $this->remoteUrl);

        return $this->begin()->run("git push {$tokenizedRepoUrl}", $params)->end();
    }

    /**
     * @throws GitException
     */
    public function pushTags(): self
    {
        $tokenizedRepoUrl = $this->getTokenizedRemoteUrl($this->accessToken, $this->remoteUrl);

        return $this->begin()->run("git push --tags {$tokenizedRepoUrl}")->end();
    }

    public function pushMainBranchToOrigin(): self
    {
        return $this->begin()->run("git push origin", [$this->mainBranch])->end();
    }

    /**
     * Clone remote repository to local dir.
     * `git clone <repo_path> <dir>`
     *
     * @throws GitException
     */
    public function cloneRemoteRepository(string $remoteRepo, string $dir): self
    {
        $tokenizedRepoUrl = $this->getTokenizedRemoteUrl($this->accessToken, $remoteRepo);

        return $this->begin()
            ->run("git clone {$tokenizedRepoUrl} \"$dir\"")
            // remove PAT from git remote url after clone for secure reasons
            ->run("cd \"{$dir}\" && git remote set-url origin {$remoteRepo} && cd ..")
            ->end();
    }
    
    /**
     * @param string $cmd
     * @param string|Closure|callable $filterCallback
     *
     * @return NULL|\string[]
     * @throws GitException
     */
    protected function extractFromCommand(string $cmd, $filterCallback = null)
    {
        $output = [];
        $exitCode = null;
        
        $this->begin();
        $this->fs->exec($cmd, $output, $exitCode, __METHOD__);
        $this->end();

        if ($exitCode !== 0 || !is_array($output)) {
            $this->exception("Command $cmd failed.");
        }
        
        if ($filterCallback && is_callable($filterCallback)) {
            $newArray = [];
            
            foreach ($output as $line) {
                $value = $filterCallback($line);
                if ($value === false) {
                    continue;
                }
                
                $newArray[] = $value;
            }
            
            $output = $newArray;
        }
        
        if (!isset($output[0])) { // empty array
            return null;
        }
        
        return $output;
    }
    
    /** Runs command.
     *
     * @throws GitException
     */
    protected function run(string $cmd, ...$args): self
    {
        $cmd  = $this->processCommand($cmd, $args);
        $this->fs->exec($cmd, $output, $ret, __METHOD__);
        $this->lastOutput = is_string($output) ? $output : implode("\n", $output);
        
        if ($ret !== 0) {
            $this->exception("Command '$cmd' failed on " . $this->repository , $output);
        }
        
        return $this;
    }
    
    protected function processCommand(string $programName, array $args): string
    {
        $cmd = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    $_c = '';

                    if (is_string($key)) {
                        $_c = "$key ";
                    }

                    $cmd[] = $_c . escapeshellarg($value);
                }
            } elseif (is_scalar($arg) && !is_bool($arg)) {
                $cmd[] = escapeshellarg($arg);
            }
        }

        $sshParams = '';

        if ($this->sshKeyPath) {
            $sshParams .= ' -i ' . $this->sshKeyPath . ' ';
        }

        if ($this->ignoreKnownHosts) {
            $sshParams .= ' -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ';
        }

        if ($sshParams) {
            $sshParams = ' GIT_SSH_COMMAND="ssh ' . $sshParams . '" ';
        }

        $gitAuthor = '';
        if ($this->commitAuthorName && $this->commitAuthorEmail) {
            $gitName = " export GIT_AUTHOR_NAME=\"$this->commitAuthorName\" ";
            $gitEmail = " export GIT_AUTHOR_EMAIL=\"$this->commitAuthorEmail\" ";
            $gitAuthor = "$gitName && $gitEmail && ";
        }

        $commandLine = trim("{$sshParams} {$programName} " . implode(' ', $cmd));

        // wrap command with brackets to run it as subshell and avoid impact
        // of exported variables on main shell
        return "( {$gitAuthor} {$commandLine} )";
    }

    /**
     * Clones GIT repository from $url into $directory
     *
     * @throws GitException
     */
    public function cloneLocalRepository(string $localPath, ?string $targetPath, bool $saveOriginalRemote = true): bool
    {
        if ($targetPath !== null && is_dir("$targetPath/.git")) {
//            $this->exception("Repo already exists in $targetPath.");
            return false;
        }
        
        $cwd = getcwd();
        $this->cwd = $cwd;
        
        if (!self::isAbsolute($targetPath)) {
            $targetPath = "$cwd/$targetPath";
        }
    
        $this->fs->exec('git clone -q ' . escapeshellarg($localPath) . ' ' . escapeshellarg($targetPath), $output, $returnCode, __METHOD__);
        
        if ($returnCode !== 0) {
            $this->exception("Git clone failed (directory $targetPath).");
        }
        
        if ($saveOriginalRemote) {
            $localFs = new Fs($localPath);
            $originalRemote = $localFs->exec('git remote get-url origin', $output, $result, __METHOD__);
            
            if ($originalRemote) {
                $originFs = new Fs($targetPath);
                $setRemote = 'git remote set-url origin ' . $originalRemote;
                App::i()->log($originalRemote, __METHOD__);
                $originFs->exec($setRemote, $output, $returnCode, __METHOD__);
            }
        }
        
        $this->cwd = null;
        
        if ($returnCode !== 0) {
            $this->exception("Git clone failed (directory $targetPath).");
        }
        
        return true;
    }

    public function update(string $branch): string
    {
        $tokenizedRemoteUrl = $this->getTokenizedRemoteUrl($this->accessToken, $this->remoteUrl);

        $output = [];
        $output[] = $this->begin()->run("git fetch -p {$tokenizedRemoteUrl} 2>&1")->getLastOutput();
        $output[] = $this->begin()->run("git merge --ff-only -X theirs origin/{$branch} 2>&1")->getLastOutput();
        $output[] = $this->begin()->run("touch ./")->getLastOutput();

        return implode("\n", $output);
    }

    /**
     * Is path absolute?
     * Method from Nette\Utils\FileSystem
     * @link   https://github.com/nette/nette/blob/master/Nette/Utils/FileSystem.php
     */
    public static function isAbsolute(string $path): bool
    {
        return (bool)preg_match('#[/\\\\]|[a-zA-Z]:[/\\\\]|[a-z][a-z0-9+.-]*://#Ai', $path);
    }
    
    public function getPath(): string
    {
        return $this->path;
    }
    
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @throws GitException
     */
    private function exception(string $msg, array $output = [])
    {
        $this->end();
        App::i()->getLogger()->error("Error: $msg", ['output' => $output]);
        $e = new GitException($msg);
        $e->setOutput($output);
        throw $e;
    }
    
    public function getLastOutput(): string
    {
        return $this->lastOutput;
    }

    /**
     * @throws GitException
     */
    public function diff(): string
    {
        $this->run('git diff');
        return $this->lastOutput;
    }
    
    public function getBehindStatus($branch)
    {
        $this->begin()->run("git rev-list --left-right --count origin/{$this->mainBranch}...{$branch}")->end();
        return preg_split('/\s+/', $this->lastOutput);
    }

    public function getSshKeyPath(): string
    {
        return $this->sshKeyPath;
    }
    
    public function setSshKeyPath(?string $sshKeyPath): void
    {
        $this->sshKeyPath = $sshKeyPath;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
