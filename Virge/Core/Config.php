<?php
namespace Virge\Core;

use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
class Config 
{
    
    protected static $_config = null;

    public static function env($key, $default = null)
    {
        $envValue = getenv($key);

        return $envValue !== false ? $envValue : $default;
    }
    
    public static function get($configName, $key = null, $defaultValue = null) 
    {
        
        if(!self::$_config){
            self::setupConfig();
        }
            
        $config = self::$_config[$configName] ?? null;

        if(!$key){
            return $config;
        }

        if(!$config) {
            return $defaultValue;
        }

        return $config[$key] ?? $defaultValue;
    }
    
    public static function setupReactor(BaseReactor $reactor)
    {
        $reflector = new \ReflectionClass($reactor);
        $appPath = dirname($reflector->getFileName()) . '/';
        
        $basePath = str_replace('/app', '', $appPath);

        self::setupConfig($basePath, $appPath);
    }
    /**
     * Setup our configs
     */
    public static function setupConfig($baseDir = '', $appDir = '')
    {
        if(is_file($appDir . 'config/_compiled.php')) {
            self::$_config = unserialize(file_get_contents($appDir . 'config/_compiled.php'));
            
            if(self::$_config !== false) {
                return;
            }
        }
        
        $config = [];
        
        //load config file(s)
        $configPath = $appDir . 'config/';
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
        $config['base_path'] = $baseDir;
        $config['app_path'] = $appDir;
        $config['config_path'] = $configPath;
        self::$_config = $config;
        
        foreach(self::$_config as $configType => $config) {
            self::$_config[$configType] = self::replaceConfigVariables($config);
        }
    }
    
    /**
     * Replace variables
     * @param array $config
     * @return array
     */
    protected static function replaceConfigVariables($config)
    {
        
        if(!is_array($config)){
            return $config;
        }
        
        $replacements = array(
            '{year}'        =>      date('Y'),
            '{month}'       =>      date('m'),
            '{day}'         =>      date('d'),
            '{hour}'        =>      date('H'),
            '{minute}'      =>      date('i'),
            '{second}'      =>      date('s'),
        );
        
        foreach($config as $key => $value) {
            if(is_array($value)){
                $config[$key] = self::replaceConfigVariables($value);
            }
            if(is_string($value)){
                $config[$key] = str_replace(array_keys($replacements), array_values($replacements), $value);
            }
        }
        
        return $config;
    }
    
    /**
     * Take in a filename and return it without the extension
     * @param string $file
     * @return string
     */
    protected static function getConfigNameFromFile($file)
    {
        return str_replace(array('.php.dist', '.php', '.dist'), '', strtolower($file));
    }
    
    /**
     * Get absolute path
     * @param string $path
     */
    public static function path($capsulePath)
    {
        $data = explode('@', $capsulePath);
        
        $capsule = $data[0];
        $path = isset($data[1]) ? $data[1] : '';
        
        //TODO: look up cached path
        try {
            $reflector = new \ReflectionClass($capsule . '\\Capsule');
        } catch( \Exception $ex) {
            $capsuleData = explode('\\', $capsule);
            $capsuleName = end($capsuleData);
            $reflector = new \ReflectionClass($capsule . '\\' . $capsuleName . 'Capsule');
        }
        $capsuleDir = dirname($reflector->getFileName()) . '/';
        
        return $capsuleDir . $path;
    }
    
    /**
     * Compile the config
     */
    public static function compile()
    {
        $configPath = self::get('app_path') . 'config/';
        $configFiles = Virge::dirToArray($configPath);
        
        $distConfigs = array();
        
        if($configFiles){
            foreach($configFiles['file'] as $configFile) {
                if(strpos($configFile, '.dist') === false) {
                    continue;
                }
                $configName = self::getConfigNameFromFile($configFile);
                $distConfigs[$configName] = include_once $configPath . $configFile;
            }
        }
        
        $compiledConfig = array();
        foreach(self::$_config as $name => $config) {
            if(isset($distConfigs[$name])) {
                $distConfig = $distConfigs[$name];
                $compiledConfig[$name] = array_replace_recursive($distConfig, $config);
            } else {
                $compiledConfig[$name] = $config;
            }
        }
        
        file_put_contents($configPath . '_compiled.php', serialize($compiledConfig));
    }
}