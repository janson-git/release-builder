<?php

namespace Service;

use Admin\App;
use Commands\Command\Pack\GitCreateTag;
use Commands\Command\SlotDeploy;
use Commands\CommandContext;
use Git\GitRepository;
use Commands\Command\LocalDeploy;
use Commands\Command\CommandProto;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\Command\Pack\ConflictAnalyzeCommand;
use Commands\Command\Pack\FetchSandbox;
use Commands\Command\Pack\CheckpointMergeBranches;
use Commands\Command\Pack\GitMergeToMaster;
use Commands\Command\Pack\GitPushCheckpoint;
use Commands\Command\Pack\RemoveCheckpoint;
use Commands\Command\Pack\RemovePackWithData;

class Pack
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    
    /**
     * @var Node
     */
    protected $node;
    
    protected $sandboxPath;
    
    protected $projectId;
    
    protected $dirsToInit = [];
    
    /** @var GitRepository[] */
    protected $repos = [];
    
    protected $mergeResults = [];
    
    private $branches = [];
    
    private $data;
    
    /** @var Checkpoint[] */
    private $checkPoints = [];
    
    protected $error = '';
    
    protected $allowPush = true;
    
    /** @var Project */
    protected $project;
    
    /**
     * Sandbox constructor.
     */
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
            throw new \Exception('Call '.__FUNCTION__.' without "name" set');
        }
        
        $projectDir = $this->getProject()->getNameQuoted();
        $projectRelativePath = DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . $name;

        return $this->sandboxPath . $projectRelativePath;
    }
    
    public function prepareCommand (CommandProto $command): CommandProto
    {
        $context = $command->getContext();
        $context->setPack($this);
        
        $lastCheckpoint = $this->getLastCheckPoint() ?: null;
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
    public function prepareCommands(array $commands): array
    {
        $lastCheckpoint = $this->getLastCheckPoint() ?: null;
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
     * @param $command CommandProto
     */
    public function runCommand (CommandProto $command): void
    {
        $command->getContext()->setPack($this);
        $command->prepare();
        $command->run();
    }
    
    /**
     * @return CommandProto[]
     */
    public function getCheckpointCommands(): array
    {
        /* @var $commands CommandProto[] */
        $commands = [
//            new LocalDeploy(),
            new CheckpointMergeBranches(),
            new ConflictAnalyzeCommand(),
            //            new BuildReleaseByDirectories(),
            new RemoveCheckpoint(),
        ];
        
        return $this->prepareCommands($commands);
    }
    
    public function getPackCommands(): array
    {
        /* @var $commands CommandProto[] */
        $commands = [
            new CheckpointCreateCommand(),
            new FetchSandbox(),
            new GitCreateTag(),
        ];
        
        if ($this->getLastCheckPoint() && $this->allowPush) {
            $commands[] = new GitPushCheckpoint();
//            $commands[] = new GitMergeToMaster();
        }

        $commands[] = new RemovePackWithData();
        
        return $this->prepareCommands($commands);
    }
    
    public function getDeployCommands(): array
    {
        /* @var $commands CommandProto[] */
        $commands = [];
        
        $slots = $this->getProject()->getSlotsPool()->loadProjectSlots()->getSlots();
        
        foreach ($slots as $slot) {
            $command = new SlotDeploy();
            $command->getContext()->setSlot($slot);
            $commands[] = $command;
        }
        
        return $this->prepareCommands($commands);
    }

    private function init(): self
    {
        if ($this->id === null) {
            throw new \Exception('Pack ID not defined!');
        }

        $this->data = Data::scope(App::DATA_PACKS)->getById($this->id);
        $this->projectId = $this->data['project'];

        $this->project = Project::getById($this->projectId);

        $this->branches = $this->data['branches'] ?: [];
        natsort($this->branches);
    
        $this->name = isset($this->data['name']) && $this->data['name'] ? $this->data['name']
            : $this->id;

        if (env('ALLOW_PUSH_ONLY_FOR_RELEASES')) {
            $this->allowPush = 0 === strpos($this->name, 'release_');
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
        } catch (\Exception $e) {
            $msg = 'Cannot create directory ' . $path . ' by user: "' . `whoami` . '" by reason:"' . $e->getMessage();
            throw new \Exception($msg);
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

                    $branchDate = new \DateTimeImmutable($details['date']);
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
     * @return \Git\GitRepository[]
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
    
    /**
     * @return string
     */
    public function getError()
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
    public function getCheckPoints(): array
    {
        return $this->checkPoints;
    }
    
    public function getLastCheckPoint(): ?Checkpoint
    {
        return $this->checkPoints ? end($this->checkPoints) : null; 
    }

    public function getCheckPoint($id): ?Checkpoint
    {
        return isset($this->checkPoints[$id]) ? $this->checkPoints[$id] : null;
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
    
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function save(): void
    {
        natsort($this->branches);

        $packData = [
            'name' => $this->getName(),
            'project' => $this->project->getId(),
            'branches' => $this->branches,
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
