<?php

namespace Interaction\Api\Controller;

use Service\Data;

class ServiceConfig extends ApiProto
{
    public function indexAction()
    {
        try {
//            $env = $this->p('env');
            $service = $this->p('service_name');
            $fields  = $this->p('fields', []);
            $all     = $this->p('all', false);
            
            $fieldsIdx = array_flip($fields);
            
            $globalData   = Data::scope('service_global');
            $globalConfig = $globalData->read();
            
            $serviceConfigName = 'service_' . $service;
            $serviceData       = Data::scope($serviceConfigName);
            $serviceConfig     = $serviceData->read();
            
            $config = $all 
                ? $serviceConfig + $globalConfig
                : array_intersect_key($serviceConfig + $globalConfig, $fieldsIdx);
        
            if ($fieldsIdx) {
                $requestLog = Data::scope($serviceConfigName.'_requests')
                    ->setData($fieldsIdx)
                    ->write(false);
            }
            
            $this->response([
                'data'                 => $config,
                'service_config_found' => !empty($serviceConfig),
                'service_config_name'  => $serviceConfigName,
            ]);
                
        } catch (\Exception $e) {
            $this->response([
                'error' => 1,
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
