<?php

namespace Virge\Core;

use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
abstract class BaseReactor 
{
    protected $_capsules = [];

    protected $_events = [];
    
    /**
     * Register different capsules that we can use
     */
    public function registerCapsules(array $capsules = []) 
    {
        Config::setupReactor($this);
        $cachePath = Config::get('base_path') . 'storage/cache/';
        if(is_file($cachePath . 'reactor.cache.php')) {
            require_once $cachePath . 'reactor.cache.php';
            $cached = true;
        } else {
            $cached = false;
            $toCache = '';
        }
        
        foreach($capsules as $capsule) {
            //todo: load anything we need to, configs, etc
            $this->_capsules[] = $capsule;
            
            if(!$cached) {
                $mainDir = $capsule->getDirectory();
                $capsuleDir = !is_dir($mainDir . '/config/') ? $this->getConfigDirectory($mainDir) : $mainDir . '/config/';
                $capsuleArray = Virge::dirToArray($capsuleDir);
                //crawl the config directory if it exists
                $files = $capsuleArray ? $capsuleArray['file'] : [];
                foreach($files as $file) {
                    $toCache .= "namespace { \n" . str_replace(array("<?php", "?>"), '', file_get_contents($capsuleDir . $file)) . " \n};" . "\n";
                    require_once $capsuleDir . $file;
                }
            }
        }
        
        foreach($capsules as $capsule) {
            $capsule->registerCapsule();
        }
        
        if(!$cached && Config::get('app', 'cache_reactor') === true) {
            //save cache
            
            if(!is_dir($cachePath)) {
                mkdir($cachePath, 0777, true);
            }
            
            if(!is_writeable($cachePath)) {
                chmod($cachePath, 0777);
            }

            file_put_contents($cachePath . 'reactor.cache.php', "<?php\n" . $toCache);
        }
    }

    public function on($eventClass, $callable)
    {
        if(!array_key_exists($eventClass, $this->_events)) {
            $this->_events[$eventClass] = [];
        }

        $this->_events[$eventClass][] = $callable;

        return $this;
    }

    protected function dispatch(Event $event)
    {
        $eventName = get_class($event);
        if(!array_key_exists($eventName, $this->_events)) {
            return;
        }

        foreach($this->_events[$eventName] as $listener)
        {
            call_user_func_array($listener, [$event]);
        }
    }
    
    protected function getConfigDirectory($directory)
    {
        $possible = [
            '/',
            '/resources/',
            Config::get('app', 'resource_dir')
        ];
        
        foreach($possible as $prefix) {
            if(is_dir($directory . $prefix . 'config/')) {
                break;
            }
        }
        
        return $directory . $prefix . 'config/';
    }
    
    /**
     * Consilidate the use statements and move them to the top
     * @param string $cache
     * @return string
     */
    protected function sanitizeCache($cache)
    {
        $lines = explode("\n", $cache);
        
        $uses = [];
        $cleanOutput = "";
        foreach($lines as $line) {
            $cleanLine = trim($line);
            
            if(strpos($cleanLine, "use ") !== false) {
                if(!in_array($cleanLine, $uses)) {
                    $uses[] = $cleanLine;
                }
                continue;
            }
            
            $cleanOutput .= $cleanLine . "\n";
        }
        
        return implode("\n", $uses) . "\n" . $cleanOutput;
        
    }

    public function bootstrap(string $environment)
    {
        Virge::setEnvironment($environment);
        Virge::setReactor($this);
        //TODO: load cache 
        $this->registerCapsules();
    }
    
    /**
     * Run our Reactor
     * @param string $environment
     * @param string $service
     * @param array $arguments
     */
    public function run(string $environment = 'dev', string $service = null, string $method = null, array $arguments = [])
    {
        $this->bootstrap($environment);
        $this->entry($service, $method, $arguments);
    }

    public function getCapsules() : array
    {
        return $this->_capsules;
    }

    /**
     * Enter into a service
     * @param string $service
     * @param array $arguments
     */
    protected function entry(string $service, string $method, array $arguments = [])
    {
        return call_user_func_array([Virge::service($service), $method], $arguments);
    }
}
