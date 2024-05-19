<?php

namespace Commands;

use Commands\Command\CommandProto;
use Commands\Command\EmptyCommand;

class CommandConfig
{
    /* pack commands */
    const PACK_CONFLICT_ANALYZE      = 'Pack\\ConflictAnalyzeCommand';
    const PACK_FETCH_PROJECT         = 'Pack\\FetchSandbox';
    const PACK_CLEAR_DATA            = 'Pack\\RemovePackWithData';
    
    /* checkpoint commands */
    const CHECKPOINT_CREATE              = 'Pack\\CheckpointCreateCommand';
    const CHECKPOINT_MERGE_BRANCHES      = 'Pack\\CheckpointMergeBranches';
    const CHECKPOINT_MERGE_TO_MASTER     = 'Pack\\GitMergeToMaster';
    const CHECKPOINT_PUSH_TO_ORIGIN      = 'Pack\\GitPushCheckpoint';
    const CHECKPOINT_CREATE_TAG          = 'Pack\\GitCreateTag';
    const CHECKPOINT_DELETE              = 'Pack\\RemoveCheckpoint';
    
    const PROJECT_FETCH_REPOS         = 'Project\\FetchProjectRepos';
    
    /* vars */
    const GLOBAL_WORK_DIR = 'workDir';
    
    public static function getCommand(string $commandId): CommandProto
    {
        $class = '\\Commands\\Command\\'.$commandId;
        
        if (class_exists($class)) {
            return new $class; 
        }
        
        return (new EmptyCommand())->setCommandName($commandId);
    }
}
