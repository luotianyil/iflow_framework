<?php


namespace iflow\log\lib;


use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{

    protected array $logs = [];
    protected string $file = '';
    protected array $config = [];

    protected string $nameSpaces = 'iflow\\log\\lib\\channels\\';
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

    protected function setLogs(string $type, $message, $content)
    {
        $content = $message. trim(var_export(count($content) <= 0 ? '' : $content, true), "'");
        $timer = \DateTime::createFromFormat('0.u00 U', microtime())->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format($this->config['time_format']);
        $this->logs[] = [
            'time' => $timer,
            'content' => $content,
            'type' => strtoupper($type)
        ];

        if (in_array($type, $this->config['errorLevelSendEmail'])) {
            // code ...
            $systemInfo = systemInfo();
            $content = "<p>{$type}: {$content}</p><p>SystemInfo: os: {$systemInfo['os']['name']}, userName: {$systemInfo['os']['user_name']}</p>";
            $content .= "<p>DateTime: {$timer}</p>";
            \Co\run(function () use ($systemInfo, $content) {
                emails($this->config['toEmails'], $content, subject: "{$systemInfo['os']['name']} - {$systemInfo['os']['user_name']} 异常提醒");
            });
        }

        return $this;
    }

    public function clear()
    {
        $this->logs = [];
        return true;
    }
}