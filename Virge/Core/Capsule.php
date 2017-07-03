<?php
namespace Virge\Core;
use Virge\Virge;

/**
 * 
 * @author Michael Kramer
 */
abstract class Capsule 
{
    protected $directory;
    
    /**
     * Register this capsule with the reactor
     */
    public abstract function registerCapsule();
    
    /**
     * Register a handler with a given short-name, which maps to the given 
     * class
     * @param string $serviceName
     * @param string $serviceClass
     */
    public static function registerService($serviceName, $serviceClass) {
        Virge::registerService($serviceName, $serviceClass);
    }

    public function getDirectory() : string
    {
        if($this->directory) {
            return $this->directory;
        }

        //
        $reflector = new \ReflectionClass($this);

        return $this->directory = dirname($reflector->getFileName()) . '/';
    }
}