<?php

namespace Service;

use Admin\App;
use Commands\Command\Pack\GitCreateTag;
use Commands\CommandConfig;
use Commands\CommandContext;
use DateTimeImmutable;
use Exception;
use Git\GitRepository;
use Commands\Command\CommandProto;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\Command\Pack\ConflictAnalyzeCommand;
use Commands\Command\Pack\FetchSandbox;
use Commands\Command\Pack\CheckpointMergeBranches;
use Commands\Command\Pack\GitPushCheckpoint;
use Commands\Command\Pack\RemoveCheckpoint;
use Commands\Command\Pack\RemovePackWithData;

class Pack
{
    private ?int $id = null;
    private ?string $name = null;
    private ?int $projectId = null;
    private ?string $userId = null;

    protected Project $project;
    protected ?User $user = null;
    protected ?Node $node = null;

    protected string $sandboxPath;

    /** @var string[]  */
    protected array $dirsToInit = [];

    /** @var GitRepository[] */
    protected array $repos = [];
    protected array $mergeResults = [];
    private array $branches = [];

    /** @var Checkpoint[] */
    private array $checkPoints = [];

    protected $error = '';

    protected bool $allowPush = true;


    public function __construct()
    {
        $this->sandboxPath = SANDBOX_DIR;
    }

    public static function getById(int $id): self
    {
        $pack = new self();
        $pack->setId($id);

        return $pack->init();
    }

    public function getPath(): string
    {
        $name = $this->getName();
        if (!$name) {
            throw new Exception('Call '.__FUNCTION__.' without "name" set');
        }

        $projectDir = $this->getProject()->getNameQuoted();
        $projectRelativePath = DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . $name;

        return $this->sandboxPath . $projectRelativePath;
    }

    public function prepareCommand(CommandProto $command): CommandProto
    {
        $context = $command->getContext();
        $context->setPack($this);

        $lastCheckpoint = $this->getLastCheckpoint() ?: null;
        if ($lastCheckpoint) {
            $context->setCheckpoint($lastCheckpoint);
        }

        $command->setContext($context);

        return $command;
    }

    /**
     * @param $commands CommandProto[]
     *
     * @return CommandProto[]
     */
    private function prepareCommands(array $commands): array
    {
        $lastCheckpoint = $this->getLastCheckpoint() ?: null;
        foreach ($commands as $command) {
            $context = $command->getContext();
            $context->setPack($this);
            if ($lastCheckpoint) {
                $context->setCheckpoint($lastCheckpoint);
            }
            $command->setContext($context);
        }

        return $commands;
    }

    /**
     * @param array|string[] $commandIds
     * @return CommandProto[]
     */
    private function getPreparedCommands(array $commandIds): array
    {
        /** @var CommandProto[] $commands */
        $commands = array_map([CommandConfig::class, 'getCommand'], $commandIds);

        return $this->prepareCommands($commands);
    }

    public function createCheckpoint (): void
    {
        $command = new CheckpointCreateCommand();
        $command->getContext()->setPack($this);
        $command->prepare();
        $command->run();
    }

    /**
     * @return CommandProto[]
     */
    public function getCheckpointCommands(): array
    {
        return $this->getPreparedCommands([
            CommandConfig::CHECKPOINT_MERGE_BRANCHES,
            CommandConfig::PACK_CONFLICT_ANALYZE,
            CommandConfig::CHECKPOINT_DELETE,
        ]);
    }

    public function getPackCommands(): array
    {
        $commands = [
            CommandConfig::CHECKPOINT_CREATE,
            CommandConfig::PACK_FETCH_PROJECT,
            CommandConfig::CHECKPOINT_CREATE_TAG,
        ];

        if ($this->getLastCheckpoint() && $this->allowPush) {
            $commands[] = CommandConfig::CHECKPOINT_PUSH_TO_ORIGIN;
//            $commands[] = CommandConfig::CHECKPOINT_MERGE_TO_MASTER;
        }

        $commands[] = CommandConfig::PACK_CLEAR_DATA;

        return $this->getPreparedCommands($commands);
    }

    private function init(): self
    {
        if ($this->id === null) {
            throw new Exception('Pack ID not defined!');
        }

        $packData = Data::scope(App::DATA_PACKS)->getById($this->id);
        $this->projectId = $packData['project'];
        $this->userId = $packData['user'] ?? null;

        $this->project = Project::getById($this->projectId);
        $this->user = $this->userId ? User::getById($this->userId) : null;

        $this->branches = $packData['branches'] ?: [];
        natsort($this->branches);

        $this->name = isset($packData['name']) && $packData['name']
            ? $packData['name']
            : $this->id;

        if (env('ALLOW_PUSH_ONLY_FOR_RELEASES')) {
            $this->allowPush = str_starts_with($this->name, 'release_');
        }

        $node = $this->project->getNode();
        $node->subLoad();
        $node->loadRepos();

        $this->node = $node;

        $this->initRepos();

        return $this;
    }

    private function initRepos(): void
    {
        $path = $this->getPath();

        try {
            if (!file_exists($path)) {
                mkdir($path, 0774, true);
                chmod($path, 0774);
            }
        } catch (Exception $e) {
            $msg = 'Cannot create directory ' . $path . ' by user: "' . `whoami` . '" by reason:"' . $e->getMessage();
            throw new Exception($msg);
        }

        $this->loadSandboxRepos();
    }

    private function loadSandboxRepos(): void
    {
        $sandboxPath = $this->getPath();
        foreach ($this->node->getRepos() as $id => $repo) {
            $sandboxRepoPath = $sandboxPath . $repo->getPath();
            if (!file_exists($sandboxRepoPath . '/.git')) {
                $this->dirsToInit[$id] = $sandboxRepoPath;
            } else {
                $this->repos[$id] = new GitRepository($sandboxRepoPath);
            }
        }
    }

    public function loadCheckpoints(): void
    {
        $branchesByProject = [];
        $branchesDetailed = [];

        foreach ($this->repos as $repo) {
            $branchesByProject[] = $repo->getBranches();
            $branchesDetailed[] = $repo->getBranchesWithDetails();
        }

        $commonLocalBranches = [];
        $latestBranchDetails = [];

        if (count($branchesByProject) > 1) {
            $commonLocalBranches = call_user_func_array('array_intersect', $branchesByProject);
            // get only the latest info as details
            foreach ($commonLocalBranches as $branchName) {
                $latestDate = null;
                foreach ($branchesDetailed as $data) {
                    $details = $data[$branchName];

                    $branchDate = new DateTimeImmutable($details['date']);
                    if ($latestDate !== null && $latestDate > $branchDate) {
                        continue;
                    }
                    $latestDate = $branchDate;
                    $latestBranchDetails[$branchName] = $details;
                }
            }
        } else if($branchesByProject) {
            $commonLocalBranches = array_filter((array)$branchesByProject[0]);
            $latestBranchDetails = $branchesDetailed[0] ?? [];
        }

        // Create list of existed checkpoints (already exist in file system)
        foreach ($commonLocalBranches as $branch) {
            if (in_array($branch, ['master','main'])) {
                continue;
            }

            $cp = new Checkpoint($this, $branch, $latestBranchDetails[$branch]);
            $cp->setCommands($this->getCheckpointCommands());

            $this->checkPoints[$branch] = $cp;
        }
    }

    public function cloneMissedRepos(): void
    {
        $repos = $this->node->getRepos();
        foreach ($this->dirsToInit as $id => $targetPath) {
            $repos[$id]->cloneLocalRepository($repos[$id]->getRepositoryPath(), $targetPath);
            $this->repos[$id] = new GitRepository($targetPath);
            unset($this->dirsToInit[$id]);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): self
    {
        $this->node = $node;
        return $this;
    }

    public function getDirsToInit(): array
    {
        return $this->dirsToInit;
    }

    /**
     * @return GitRepository[]
     */
    public function getRepos(): array
    {
        return $this->repos;
    }

    /**
     * @return array
     */
    public function getMergeResults(): array
    {
        return $this->mergeResults;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getBranches(): array
    {
        return $this->branches;
    }

    public function setBranches(array $packBranches): self
    {
        $this->branches = $packBranches;
        return $this;
    }

    /**
     * @return Checkpoint[]
     */
    public function getCheckpoints(): array
    {
        return $this->checkPoints;
    }

    public function getLastCheckpoint(): ?Checkpoint
    {
        return $this->checkPoints ? end($this->checkPoints) : null;
    }

    public function getCheckpoint($id): ?Checkpoint
    {
        return $this->checkPoints[$id] ?? null;
    }

    public function setProjectId(int $projectId): self
    {
        $this->projectId = $projectId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        $this->userId = $user->getId();
        return $this;
    }

    public function save(): void
    {
        natsort($this->branches);

        $packData = [
            'name' => $this->getName(),
            'project' => $this->project->getId(),
            'branches' => $this->branches,
            'user' => $this->userId,
        ];

        $packId = $this->getId() ?? crc32((string) microtime(true));
        $this->id = $packId;

        Data::scope(App::DATA_PACKS)
            ->insertOrUpdate($packId, $packData)
            ->write();
    }

    public function delete(): void
    {
        Data::scope(App::DATA_PACKS)
            ->delete($this->id)
            ->write();
    }
}
