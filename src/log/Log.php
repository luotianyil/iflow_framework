<?php


namespace iflow\log;


use iflow\log\lib\Logger;

class Log extends Logger {

    public object $channel;

    public function initializer()
    {
        $this->config = config('log');
        $channels = $this->nameSpaces . $this->config['default'];
        $this->channel = app() -> make($channels, [$this->config]);
    }

    public function write(string $type, string $message, array $content = []): bool
    {
        if (!method_exists($this, $type)) return false;
        call_user_func([$this, $type], ...[$message, $content]);
        return $this->save();
    }

    public function save(): bool
    {
        if ($this->channel -> save($this->logs)) {
            return $this -> clear();
        }
        return false;
    }
}