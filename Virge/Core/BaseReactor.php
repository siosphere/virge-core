<?php

namespace Virge\Core;

use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
abstract class BaseReactor {
    
    protected $_capsules = array();
    
    /**
     * Register different capsules that we can use
     * @param array $capsules
     */
    public function registerCapsules($capsules = array()) {
        //todo: check cache
        foreach($capsules as $capsule) {
            //todo: load anything we need to, configs, etc
            $this->_capsules[] = $capsule;
            
            $reflector = new \ReflectionClass(get_class($capsule));
            $capsuleDir = dirname($reflector->getFileName()) . '/config/';
            $capsuleArray = Virge::dirToArray($capsuleDir);
            //crawl the config directory if it exists
            $files = $capsuleArray ? $capsuleArray['file'] : array();
            foreach($files as $file) {
                require_once $capsuleDir . $file;
            }
        }
    }
    
    /**
     * Run our Reactor
     * @param string $environment
     * @param string $service
     * @param array $arguments
     */
    public function run($environment = 'dev', $service = null, $method = null, $arguments = array()) {
        $this->registerCapsules();
        $this->entry($service, $method, $arguments);
    }
    
    /**
     * Enter into a service
     * @param string $service
     * @param array $arguments
     */
    protected function entry($service, $method, $arguments) {
        return call_user_func_array(array(Virge::service($service), $method), $arguments);
    }
}
