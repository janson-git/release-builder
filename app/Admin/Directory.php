<?php

namespace Admin;

use Git\GitRepository;
use Service\Util\StringHelper;

class Directory
{
    protected string $sitesDir = REPOS_DIR . '/';

    public function getSitesDir(): string
    {
        return $this->sitesDir;
    }

    public function setSitesDir(string $sitesDir): void
    {
        $this->sitesDir = $sitesDir;
    }

    public function scanDir(): array
    {
        $data = $this->doScanDir($this->sitesDir);
        
        foreach ($data as $dirName => &$item) {
            $time = $this->getUpdateTime($dirName);
            $remotes = $this->getRemotes($dirName);
            $repoType = strpos($remotes[0], 'https:') > 0 ? 'https' : 'ssh';

            $item += [
                'branch' => $this->getBranch($dirName),
                'time'   => $time,
                'idx'    => $time['timestamp'],
                'com'    => $this->getLastCommit($dirName),
                'remote' => $remotes,
                'repoName' => $this->getFullRepoName($dirName),
                'type' => $repoType,
            ];
        }
        
        $keys = array_keys($data);
        $idx  = array_column($data, 'idx');
        array_multisort($idx, SORT_DESC, $data, $keys);

        return array_combine($keys, $data);
    }
    
    protected function doScanDir(string $dir, int $deep = 1, $prefix = ''): array
    {
        $dir = rtrim($dir, '/');
        $prefix = $prefix ? $prefix . '/' : '';

        $data   = [];
        if ($handle = @opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                $file = $dir . '/' . $entry;
                if ($entry != "." && $entry != ".." && is_dir($file)) {
                    $localName = $prefix . '' . $entry;
                    if (file_exists($file . '/.git')) {
                        $data[$localName] = [
                            'file' => $file,
                        ];
                    }
                    
                    if ($deep < 3) {
                        $data += (array)$this->doScanDir($file, $deep + 1, $localName);
                    }
                }
            }
            closedir($handle);
        }

        return $data;
    }
    
    public function allData(): array
    {
        return $this->scanDir();
    }
    
    public function checkDir(string $dir): bool
    {
        return is_dir($this->sitesDir . $dir);
    }
    
    /**
     * @param string $dir
     * @return mixed
     */
    public function getBranch(string $dir)
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        exec('cd ' . $this->sitesDir . $dir . ' && git branch -a', $result);
        
        return $result;
    }
    
    public function getLastCommit(string $dir)
    {
        exec('cd ' . $this->sitesDir . $dir . ' && git log -5 --pretty="%cn %B" ', $result);
        
        return array_filter($result);
    }
    
    public function getRemotes(string $dir)
    {
        exec('cd ' . $this->sitesDir . $dir . ' && git remote -v ', $result);
        
        return array_filter($result);
    }

    /**
     * Suggests that full repo name contains owner name and repo name
     * Like: owner/repo.git, janson-git/release-builder.git
     */
    public function getFullRepoName(string $dir): string
    {
        $remotes = $this->getRemotes($dir);
        $fullPath = array_shift($remotes);

        $m = [];
        if (preg_match('#\S+\s(\S+)\s.*#', $fullPath, $m)) {
            $fullPath = $m[1];
        }

        // different ways to split https and ssh links
        if (strpos($fullPath, 'https://') === 0) {
            $parts = explode('/', $fullPath);
            $nameParts = array_slice($parts, -2);
            $repoFullName = implode('/', $nameParts);
        } else {
            $repoFullName = substr($fullPath, strpos($fullPath, ':') +1 );
        }

        return $repoFullName;
    }

    public function getCurrentBranch(string $dir): string
    {
        $branches = $this->getBranch($dir);
        
        foreach ($branches as $branch) {
            if (str_starts_with($branch, '*')) {
                return trim($branch, ' *');
            }
        }
        
        return 'unknown';
    }
    
    public function getUpdateTime(string $dir): array
    {
        $time = filemtime($this->sitesDir . $dir);
        
        return [
            'back'      => StringHelper::howMuchAgo($time),
            'date'      => date('d-m h:i', $time),
            'timestamp' => $time,
        ];
    }
    
    public function update($dir): string
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        $branch = $this->getCurrentBranch($dir);

        $repo = new GitRepository($this->sitesDir . $dir);

        return $repo->update($branch);
    }
    
    public function fix($dir, $realClear = null): string
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir: ' . $this->sitesDir . $dir;
        }
        
        $result[] = '$realClear:' . json_encode($realClear);
        
        $result[] = shell_exec('cd ' . $this->sitesDir . $dir . ' && git reset --hard FETCH_HEAD 2>&1');
        
        if (!$realClear) {
            exec('cd ' . $this->sitesDir . $dir . ' && git clean -dn ', $result);
        } else {
            exec('cd ' . $this->sitesDir . $dir . ' && git clean -df ', $result);
        }
        
        return implode("\n", $result);
    }
    
    public function checkout(string $dir, string $branch): string
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        if (!$branch) {
            return 'branch not passed';
        }
        
        if (strpos($branch, '/')) {
            $array = explode('/', $branch);
            $branch = end($array) . ' ' . $branch;
        }

        $repo = new GitRepository($this->sitesDir . $dir);

        $result[] = $repo->fetch()->getLastOutput();
        $result[] = $repo->checkoutBranchOrResetAndCheckout($branch)->getLastOutput();

        return implode("\n", $result);
    }

    public function cloneRepository(string $repositoryPath, string $dirName): string
    {
        if (file_exists($this->sitesDir . $dirName)) {
            $dirName .= date('Ymd_His');
        }

        $newRepoDir = $this->sitesDir . $dirName;
        $commands = [
            ['mkdir ' . $newRepoDir, ''],
            ['pwd ', ''],
        ];

        $result = [];
        $state  = 0;

        $dir = 'cd ' . $this->sitesDir;

        $log = function ($c, $data) use (&$result) {
            $result[] = [
                'com' => $c,
                'res' => $data,
            ];
        };
        $resultToLogString = function (int $state, array $result): string {
            if (!$state) {
                $res = $result ? implode('<br>', $result) : 'ok';
            } else {
                $res = 'fail: ' . implode('<br>', $result);
            }

            return $res;
        };

        foreach ($commands as $cData) {
            list ($command, $pill) = $cData;
            // if last command was failed - let's break
            if ($state) {
                break;
            }
            if (str_starts_with($command, 'cd ')) {
                $dir = $command;
            }

            $eCommand = $dir . ' && ' . $command . ' 2>&1';

            unset ($res);
            exec($eCommand, $res, $state);
            $log($command, $resultToLogString($state, $res));

            if ($state && $pill) {
                unset ($res);
                exec($dir . ' && ' . $pill, $res, $state);
                $log($command . ' pill => ' . $pill, $resultToLogString($state, $res));

                unset ($res);
                exec($eCommand, $res, $state);
                $log($command . ' recall', $resultToLogString($state, $res));
            }
        }

        $repo = new GitRepository($this->sitesDir);
        $repo->cloneRemoteRepository($repositoryPath, $dirName);

        $log('clone remote repository', $repo->getLastOutput());

        $output = [];
        foreach ($result as $item) {
            $output[] = $item['com'] . "\n" . $item['res'];
        }
        return implode("\n", $output);
    }
}
