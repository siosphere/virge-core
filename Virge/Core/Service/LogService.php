<?php
namespace Virge\Core\Service;

class LogService
{
    const SERVICE_ID = 'virge.core.log';

    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_WARNING = 'warning';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    protected $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function log($message, $level = self::LEVEL_INFO, $data = [])
    {
        return file_put_contents($this->logFile, sprintf("[%s] [%s] %s\n%s\n", date('Y-m-d H:i:s'), $level, $message, json_encode($data)), FILE_APPEND);
    }

    public function error($message, $data = [])
    {
        return $this->log($message, self::LEVEL_ERROR, $data);
    }

    public function info($message, $data = [])
    {
        return $this->log($message, self::LEVEL_INFO, $data);
    }

    public function critical($message, $data = [])
    {
        return $this->log($message, self::LEVEL_CRITICAL, $data);
    }

    public function warning($message, $data = [])
    {
        return $this->log($message, self::LEVEL_WARNING, $data);
    }

    public function debug($message, $data = [])
    {
        return $this->log($message, self::LEVEL_DEBUG, $data);
    }

    public function exception(\Exception $ex, $data = [])
    {
        return $this->error("(".get_class($ex).": " . $ex->getMessage() . " )", $data);
    }
}