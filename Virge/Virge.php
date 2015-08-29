<?php

namespace Virge;

/**
 * Virge Framework
 * 
 */

class Virge {
    
    /**
     * What is our current release state?, this will help to know what config 
     * files to load
     * @var string
     */
    public static $releaseState = 'dev';
    
    /**
     * What is our current app?
     * @var 
     */
    protected static $_app = NULL;
    
    /**
     * Holds our handlers, models, etc
     * @var array
     */
    private static $a_DataPool;
    
    /**
     * Holds our services
     * @var array 
     */
    protected static $_services = array();
    
    /**
     * Holds our config
     * @var array
     */
    private static $config;
    
    /**
     * Register a service
     * 
     * @param string $serviceName
     * @param string $serviceClass
     * @return mixed
     */
    public static function registerService($serviceName, $serviceClass) {
        
        if(is_object($serviceClass)){
            return self::$_services[$serviceName] = $serviceClass;
        }
        
        return self::$_services[$serviceName] = new $serviceClass();
    }

    /**
     * Return all our available services
     * @return array
     */
    public static function getServices() {
        return self::$_services;
    }

    /**
     * Return a service by name
     * @param type $service_name
     * @return type
     * @throws InvalidArgumentException
     */
    public static function service($service_name) {
        if (isset(self::$_services[$service_name])) {
            return self::$_services[$service_name];
        }
        
        throw new \InvalidArgumentException('Invalid service: ' . $service_name);
    }

    /**
     * Run Everything
     * @param string $app
     * @param string $service
     * @param array $arguments
     */
    public static function run($app = 'base', $service = '', $arguments = array()) {
        
    }


    /**
     * Register a Model into the data pool
     * @param string $ident
     * @param object $object
     * @return object
     */
    public static function register($ident, $object) {
        self::$a_DataPool['model'][$ident] = $object;
        return $object;
    }

    /**
     * Return a registered model object
     * @param string $ident
     * @return object
     */
    public static function getModel($ident) {
        if (isset(self::$a_DataPool['model'][$ident])) {
            return self::$a_DataPool['model'][$ident];
        }
        return NULL;
    }

    /**
     * Directory to Array
     * @param $dir
     */
    public static function dirToArray($dir) {
        if (is_dir($dir)) {
            $temp = array();
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
    public static function xml2Array($string) {
        $xml_values = array();
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
            return array();

        $xml_array = array();
        $last_tag_ar = & $xml_array;
        $parents = array();
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
}