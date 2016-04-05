<?php
namespace Virge\Core;

use Virge\Virge;


/**
 * 
 */
abstract class BaseService implements ServiceInterface
{
    protected static function service($serviceId)
    {
        return Virge::service($serviceId);
    }
}