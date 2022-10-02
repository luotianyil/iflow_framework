<?php


namespace iflow\log\implement;


use iflow\event\lib\Abstracts\ObserverAbstract;
use Psr\Log\LoggerInterface;

class Logger extends ObserverAbstract implements LoggerInterface {

    protected array $logs = [];
    protected string $file = '';
    protected array $config = [];

    protected string $nameSpaces = 'iflow\\log\\implement\\channels\\';
    protected object $channel;

    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
        return $this->setLogs('emergency', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        // TODO: Implement alert() method.
        return $this->setLogs('alert', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
        return $this->setLogs('critical', $message, $context);
    }

    public function error($message, array $context = array())
    {
        // TODO: Implement error() method.
        return $this->setLogs('error', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        // TODO: Implement warning() method.
        return $this->setLogs('warning', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        // TODO: Implement notice() method.
        return $this->setLogs('notice', $message, $context);
    }

    public function info($message, array $context = array())
    {
        // TODO: Implement info() method.
        return $this->setLogs('info', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
        return $this->setLogs('debug', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
        return $this->setLogs('log', $message, $context);
    }

    protected function setLogs(string $type, $message, $content): static
    {
        $content = $message. trim(var_export(count($content) <= 0 ? '' : $content, true), "'");
        $timer = \DateTime::createFromFormat('0.u00 U', microtime())->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format($this->config['time_format']);
        $this->logs[] = [ 'time' => $timer, 'content' => $content, 'type' => strtoupper($type) ];
        return $this;
    }

    public function clear(): bool {
        $this->logs = [];
        return true;
    }
}