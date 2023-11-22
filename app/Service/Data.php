<?php

namespace Service;

use Admin\App;

class Data
{
    const DEFAULT_DATA_DIR         = 'data';
    const DEFAULT_DELETED_DATA_DIR = 'data_deleted';
    
    const MASTER_FILE = 'master.json';
    
    protected static array $dataDirChecked = [];
    protected static array$scopes = [];
    
    protected string $scope = 'test';
    protected string $workDir  = '';
    protected string $modifier = 'system';
    protected array $data = [];
    protected string $string = '';
    protected string $dataDir = self::DEFAULT_DATA_DIR;
    
    protected static array $cache = [];
    private static array $scopedInstances = [];
    
    public function getScopes($reload = false): array
    {
        $dataDir = self::DEFAULT_DATA_DIR;

        if (!self::$scopes || $reload) {
            $this->checkDataDir();
            self::$scopes = scandir($dataDir);
            
            foreach (self::$scopes as $k => $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($dataDir . '/' . $dir)) {
                    unset(self::$scopes[$k]);
                }
            }
        }
        
        return self::$scopes;
    }
    
    
    public function __construct(string $scope, bool $autoCreate = true)
    {
        self::checkDataDir();
        $this->scope = $scope;
        $this->dataDir = self::DEFAULT_DATA_DIR;
        $this->initScope($autoCreate);
    }

    public static function scope(string $scopeName): self
    {
        if (!isset(self::$scopedInstances[$scopeName])) {
            self::$scopedInstances[$scopeName] = new self($scopeName);
        }
        return self::$scopedInstances[$scopeName];
    }

    public function lock()
    {
        
    }
    
    public function unlock()
    {
        
    }
    
    public function write(bool $writeVersion = true): bool
    {
        $this->string = json_encode($this->data, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
        
        if ($writeVersion) {
            $fileName     = $this->getVersionFileName();
            $file         = $this->getFile($fileName);
            $start = microtime(1);
            file_put_contents($file, $this->string);
            App::i()->log('Writing file: '.$file, __METHOD__, $start);
            unset(self::$cache[$this->workDir][$fileName]); //clear cache
        }
        
        return $this->commit();
    }

    public function getById($id, $default = null)
    {
        return $this->readCache()[$id] ?? $default;
    }

    public function getAll(): array
    {
        return $this->readCache();
    }

    public function getWhere($field, $value): array
    {
        $data = $this->readCache();
        $result = [];
        foreach ($data as $id => $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemValue = $item[$field] ?? null;
            if ($itemValue == $value) {
                $result[$id] = $item;
            }
        }

        return $result;
    }

    public function insertOrUpdate($id, $data): self
    {
        $this->reload();

        if (!array_key_exists($id, $this->data)) {
            $this->data[$id] = $data;
        } else {
            $item = $this->data[$id];
            if (is_array($data)) {
                foreach ($data as $field => $value) {
                    $item[$field] = $value;
                }

                $this->data[$id] = $item;
            } else {
                $this->data[$id] = $data;
            }
        }

        return $this;
    }

    public function delete($id): self
    {
        $this->reload();

        if (array_key_exists($id, $this->data)) {
            unset($this->data[$id]);
        }

        return $this;
    }

    /**
     * Cleans cache and reload data from file
     * @return $this
     */
    public function reload(): self
    {
        $file = self::MASTER_FILE;

        self::$cache[$this->workDir][$file] = $this->read($file);
        return $this;
    }
    
    public function readCachedIdAndWriteDefault ($id, $default = '')
    {
        $this->readCache();
        
        if (isset($this->data[$id])) {
            return $this->data[$id]; 
        }
        
        $this->read();
        $this->data[$id] = $default;
        $this->write();
        
        return $default;
    }
    
    public function read(string $fileName = self::MASTER_FILE): array
    {
        $file = $this->getFile($fileName);
        
        $start = microtime(1);
        $this->string = file_exists($file) ? file_get_contents($file) : '{}';
        $this->data   = json_decode($this->string, true);

        // log caller
        $caller = $this->getCaller();

        App::i()->log("Reading file: {$file}", $caller, $start);
        
        if (!is_array($this->data)) {
            $this->data = [];
        }
        
        return $this->data;
    }

    public function rename(string $name): bool
    {
        $newDirName = $this->getDir($name, false);
        try {
            rename($this->workDir, $newDirName);
            $this->scope   = $name;
            $this->workDir = $newDirName;
            $this->initScope();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function setModifier(string $modifier): self
    {
        $this->modifier = $modifier;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        foreach ($this->data as $key => $value) {
            if(!$key) {
                unset($this->data[$key]);
            }
        }
        
        return $this;
    }

    public function getName(): string
    {
        return $this->scope;
    }
    
    public function isExist(): bool
    {
        return file_exists($this->workDir);
    }
    
    public function remove(): bool
    {
        $newDirName = $this->getDir($this->scope, true, self::DEFAULT_DELETED_DATA_DIR);
        
        try {
            rename($this->workDir, $newDirName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function readCache(): array
    {
        $file = self::MASTER_FILE;
        if (!isset(self::$cache[$this->workDir][$file])) {
            self::$cache[$this->workDir][$file] = $this->read($file);
        }

        return $this->data = self::$cache[$this->workDir][$file];
    }

    private function commit(): bool
    {
        $file = $this->getFile(self::MASTER_FILE);
        unset(self::$cache[$this->workDir][self::MASTER_FILE]); //clear cache
        return (bool) file_put_contents($file, $this->string) !== false;
    }

    private function getFile(string $file): string
    {
        $name = $this->workDir . '/' . $file;

        if (!is_writeable($this->workDir)) {
            throw new \Exception('Target file not writable: ' . $name . ' by user ' . shell_exec('whoami'));
        }

        return $name;
    }

    private function getDir(string $dirName, bool $authCreate = true, string $dataDir = self::DEFAULT_DATA_DIR)
    {
        $dir = $dataDir . '/' . $dirName;

        if ($authCreate && !file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);

            if (!file_exists($dir)) {
                throw new \Exception('Cannot create dir: ' . $dir);
            }
        }

        return $dir;
    }

    private function getVersionFileName(): string
    {
        return date('H.i.s_d-m-Y') . '_' . $this->modifier . '.json';
    }

    private function initScope($autoCreate = true): void
    {
        $this->workDir = $this->getDir($this->scope, $autoCreate);
    }

    private function checkDataDir(): void
    {
        if (!isset(self::$dataDirChecked[$this->dataDir]) && !file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
            chmod($this->dataDir, 0777);
            self::$dataDirChecked[$this->dataDir] = true;
        }
    }

    private function getCaller(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        // find first item in backtrace that is not Data class
        $caller = [];
        foreach ($backtrace as $stepNum => $info) {
            $file = $info['file'] ?? '';
            if ($file === __FILE__) {
                continue;
            }

            $caller[] = $info['class'] ?? '';
            $caller[] = $info['type'] ?? '';
            $caller[] = $info['function'] ?? '';
            if (array_key_exists('file', $info)) {
                $file = ltrim( str_replace(ROOT_DIR, '', $info['file']), '/\\');
                $caller[] = ' in ' . $file . ' ' . $info['line'];
            }
            break;
        }

        return implode($caller);
    }
}
