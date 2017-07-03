<?php
namespace Virge;

use Virge\Core\{
    BaseReactor,
    Capsule
};

use Virge\Core\Exception\{
    InvalidServiceException
};

/**
 * Virge Framework
 * 
 */
class Virge 
{
    protected static $environment = '';

    /**
     * Holds capsules, models, etc
     * @var array
     */
    private static $registry = [];
    
    /**
     * Holds our services
     * @var array 
     */
    protected static $services = [];

    protected static $capsules = [];

    protected static $reactor;
    
    /**
     * Holds our config
     * @var array
     */
    private static $config;

    public static function setReactor(BaseReactor $reactor)
    {
        self::$reactor = $reactor;
    }

    public static function reactor() : BaseReactor 
    {
        return self::$reactor;
    }

    public static function setEnvironment(string $environment)
    {
        self::$environment = $environment;
    }

    public static function getEnvironment() : string 
    {
        return self::$environment;
    }
    
    /**
     * Register a service
     */
    public static function registerService(string $serviceName, $serviceClass) 
    {
        if(is_object($serviceClass)){
            return self::$services[$serviceName] = $serviceClass;
        }
        
        return self::$services[$serviceName] = new $serviceClass();
    }

    /**
     * Register a capsule
     */
    public static function registerCapsule(Capsule $capsule)
    {
        if(!self::$registry['capsule']) {
            self::$registry['capsule'] = [];
        }

        return self::$registry['capsule'][get_class($capsule)] = $capsule;
    }

    public static function getCapsules() : array 
    {
        return self::$registry['capsule'] ?? [];
    }

    /**
     * Return all our available services
     */
    public static function getServices() : array
    {
        return self::$services;
    }

    /**
     * Return a service by name
     * @throws InvalidServiceException
     */
    public static function service(string $serviceName) {

        if (!array_key_exists($serviceName, self::$services)) {
            throw new InvalidServiceException('Invalid service: ' . $service_name);
        }

        return self::$services[$serviceName];
    }

    /**
     * Register an item into the registry
     */
    public static function register(string $key, $value) 
    {
        self::$registry['store'][$key] = $value;

        return $value;
    }

    public static function get(string $key)
    {
        if(!array_key_exists($key, self::$registry['store'])) {
            return null;
        }

        return self::$registry['store'][$key];
    }

    /**
     * @deprecated
     */
    public static function getModel(string $key) 
    {
        return self::get($key);
    }

    /**
     * Directory to Array
     * @param $dir
     */
    public static function dirToArray($dir)
    {
        if (is_dir($dir)) {
            $temp = [];
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if (is_dir($dir . $file)) {
                        $type = 'dir';
                    } else {
                        $type = 'file';
                    }
                    if ($file != '.' && $file != '..' && $file != '.svn') {
                        $temp[$type][] = $file;
                    }
                }
                closedir($handle);
            }
            return $temp;
        }
    }

    /**
     * Return config XML as an array
     * XML 2 ARRAY
     * @author vladimir_wof_nikolaich_dot_ru
     * @param string $string
     */
    public static function xml2Array($string) 
    {
        $xml_values = [];
        $contents = $string;
        $parser = xml_parser_create('');
        if (!$parser)
            return false;

        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values)
            return [];

        $xml_array = [];
        $last_tag_ar = & $xml_array;
        $parents = [];
        $last_counter_in_tag = array(1 => 0);
        foreach ($xml_values as $data) {
            switch ($data['type']) {
                case 'open':
                    $last_counter_in_tag[$data['level'] + 1] = 0;
                    $new_tag = array('name' => $data['tag']);
                    if (isset($data['attributes']))
                        $new_tag['attributes'] = $data['attributes'];
                    if (isset($data['value']) && trim($data['value']))
                        $new_tag['value'] = trim($data['value']);
                    $last_tag_ar[$last_counter_in_tag[$data['level']]] = $new_tag;
                    $parents[$data['level']] = & $last_tag_ar;
                    $last_tag_ar = & $last_tag_ar[$last_counter_in_tag[$data['level']] ++];
                    break;
                case 'complete':
                    $new_tag = array('name' => $data['tag']);
                    if (isset($data['attributes']))
                        $new_tag['attributes'] = $data['attributes'];
                    if (isset($data['value']) && trim($data['value']))
                        $new_tag['value'] = trim($data['value']);

                    $last_count = count($last_tag_ar) - 1;
                    $last_tag_ar[$last_counter_in_tag[$data['level']] ++] = $new_tag;
                    break;
                case 'close':
                    $last_tag_ar = & $parents[$data['level']];
                    break;
                default:
                    break;
            }
        }
        return $xml_array;
    }
    
    /**
     * Provide an anonymous function wrapper around a virge service for
     * easily including in callback functions, such as array_map, and 
     * usort
     * @param string $serviceId
     * @param string $method
     */
    public static function callback(string $serviceId, string $method) 
    {
        return function() use ($serviceId, $method) {
            return call_user_func_array(array(Virge::service($serviceId), $method), func_get_args());
        };
    }
}