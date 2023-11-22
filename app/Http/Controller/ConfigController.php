<?php

namespace App\Http\Controller;

use Psr\Http\Message\ResponseInterface;
use Service\Data;

/**
 * Controller to edit configs as text files
 *     (it is about configs which stored in data/ folder)
 * // TODO: looks like not necessary feature!
 * // TODO:   Right now it can be used to remove projects from files. And can
 * // TODO:   ruins app very easy with wrong config
 */
class ConfigController extends AbstractController
{
    public function isEnabled(): bool
    {
        return env('ENABLE_EDIT_CONFIGURATIONS', false);
    }

    public function index(): ResponseInterface
    {
        $data = new Data('', false);
        $scopes = $data->getScopes(Data::DEFAULT_DATA_DIR);
        
        return $this->view->render('config/index.blade.php', [
            'scopes' => $scopes,
        ]);
    }
    
    public function edit(): ResponseInterface
    {
        $scopeName = $this->p('scope');
        
        $scope = Data::scope($scopeName);

        if ($this->app->getRequest()->isPost()) {
            $keys = $this->p('data_key');
            $values = $this->p('data_value');
            
            foreach ($values as &$value) {
                $value = trim($value);
                if ($value && ($value[0] === '[' || $value[0] === '{')) {
                    $decode = json_decode($value, 1);
                    if ($decode !== null) {
                        $value = $decode;
                    }    
                }
            }
            
            $data = $keys && $values ? array_combine($keys, $values) : [];
            $scope->setData($data);
            $scope->write();
        } else {
            $scope->read();
        }
        
        return $this->view->render('config/edit.blade.php', [
            'scope' => $scopeName,
            'data' => $scope->getAll(),
        ]);
    }
    
    public function settings()
    {
        $scopeName = $this->p('scope');
        $scope = new Data($scopeName, false);
        
        
        if ($this->app->getRequest()->isPost()) {
            $action = $this->p('action');
            
            if ($action == 'changeName') {
                $name = $this->p('name');
                if ($name) {
                    $scope->rename($name);
                }
            }
            
            if($action == 'remove') {
                $scope->remove();
            }
        }
    
        return $this->view->render('config/settings.blade.php', [
            'is_exists' => $scope->isExist(),
            'scope' => $scope->getName(),
            'data' => $scope->getAll(),
        ]);
    }
    
}
