<?php


namespace iflow\log;


use iflow\facade\Event;
use iflow\log\implement\Logger;
use SplSubject;

class Log extends Logger {

    public object $channel;

    public function initializer() {
        $this->config = config('log');
        $channels = $this->nameSpaces . ucwords($this->config['default']);
        $this->channel = app() -> make($channels, [$this->config[$this->config['default']]]);

        // 订阅事件
        Event::getEvent('RequestEndEvent') ?-> attach($this);
    }

    /**
     * @param string $type 日志类型
     * @param string $message 日志信息
     * @param array $content 日志拓展信息
     * @return Logger
     */
    public function write(string $type, string $message, array $content = []): Logger {
        if (!method_exists($this, $type)) return $this;
        return call_user_func([$this, $type], $message, $content);
    }

    /**
     * 结束请求时写入日志
     * @param SplSubject|null $subject
     * @return void
     */
    public function update(?SplSubject $subject = null): void {
        if ($this->channel -> save($this->logs)) {
            $content = '';
            $timer = \DateTime::createFromFormat('0.u00 U', microtime())->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format($this->config['time_format']);
            foreach ($this -> logs as $log) {
                if (in_array($log['type'], $this->config['errorLevelSendEmail'])) {
                    $content .= "<p>Logger: {$log['content']} </p>";
                }
            }

            // 发送异常通知邮件
            if (isset($this->config['toEmails']) && $content !== '') {
                $content .= "<p>DateTime: $timer</p>";
                go(function () use ($content) { emails($this->config['toEmails'], $content, subject: "异常提醒"); });
            }
            $this->clear();
        }
    }
}