<?php
namespace Virge\Core;

use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
class Config {
    
    protected static $_config = null;
    
    public static function get($name, $key = null) {
        
        if(self::$_config){
            return isset(self::$_config[$name]) ? self::$_config[$name] : null;
        }
        //TODO: check cache
        $config = array();
        
        $reflector = new \ReflectionClass('Reactor');
        $appPath = dirname($reflector->getFileName()) . '/';
        
        $basePath = str_replace('/app', '', $appPath);
        
        //load config file(s)
        $configPath = $appPath . 'config/';
        $configFiles = Virge::dirToArray($configPath);
        if($configFiles){
            foreach($configFiles['file'] as $configFile) {
                if(strpos($configFile, '.dist') !== false) {
                    continue;
                }
                $configName = self::getConfigNameFromFile($configFile);
                $config[$configName] = include_once $configPath . $configFile;
            }
        }
        
        //setup paths
        $config['base_path'] = $basePath;
        $config['app_path'] = $appPath;
        $config['config_path'] = $configPath;
        self::$_config = $config;
        
        $config = isset(self::$_config[$name]) ? self::$_config[$name] : null;
        if(!$config || !$key){
            return $config;
        }
        
        return isset($config[$key]) ? $config[$key] : null;
    }
    
    /**
     * Take in a filename and return it without the extension
     * @param string $file
     * @return string
     */
    protected static function getConfigNameFromFile($file) {
        return str_replace('.php', '', strtolower($file));
    }
    
    /**
     * Get absolute path
     * @param string $path
     */
    public static function path($capsulePath) {
        $data = explode('@', $capsulePath);
        
        $capsule = $data[0];
        $path = $data[1];
        
        //TODO: look up cached path
        $reflector = new \ReflectionClass($capsule . '\\Capsule');
        $capsuleDir = dirname($reflector->getFileName()) . '/';
        
        return $capsuleDir . $path;
    }
}