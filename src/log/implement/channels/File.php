<?php


namespace iflow\log\implement\channels;

class File {

    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function save(array $logs): bool
    {
        $info = '';
        foreach ($logs as $key => $value) {
            $info .=
                $this->config['json'] ? json_encode($value, true) :
                    sprintf($this->config['format'], $value['time'], $value['type'], $value['content']);
            $info .= PHP_EOL;
        }
        if ($info !== '') return $this->write($info);
        return false;
    }

    protected function write(string $logs = ''): bool
    {
        $logPath = $this->getLogFile();
        $path = dirname($logPath);
        !is_dir($path) && mkdir($path, 0755, true);
        return error_log($logs, 3, $logPath);
    }

    public function getLogFile(): string
    {
        $logPath = $this->config['logPath']. DIRECTORY_SEPARATOR .date('Ym');
        return $logPath. DIRECTORY_SEPARATOR . date('d'). '.' . ($this->config['json'] ? 'json' : 'log');
    }
}