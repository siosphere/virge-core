<?php
namespace Virge\Core;

use Virge\Virge;


/**
 * 
 */
abstract class BaseService implements ServiceInterface
{
    public static function instance()
    {
        $className = static::class;

        $service = Virge::service($className);
        if($service) {
            return $service;
        }
        $service = new $className(...static::_instanceProps());

        Virge::registerService($className, $service);

        return $service;
    }

    protected static function service($serviceId)
    {
        return Virge::service($serviceId);
    }

    protected static function _instanceProps()
    {
        return [];
    }
}