<?php
use Virge\Core\Config;
use Virge\Core\Service\{
    LogService
};
use Virge\Virge;

Virge::registerService(LogService::SERVICE_ID, new LogService(Config::get('app', 'log_file') ?? '/tmp/virge.log'));