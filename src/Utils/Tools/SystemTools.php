<?php


namespace iflow\Utils\Tools;


class SystemTools
{

    public function getSystemInfo(): array
    {
        $cpuInfo = $this->getCpuInfo();
        $memeInfo = $this->getMemInfo();
        return [
            'os' => [
                'name' => php_uname('s'). ' - ' . ($_SERVER['DESKTOP_SESSION'] ?? ''),
                'version' => php_uname('r'),
                'user_name' => $_SERVER['USERNAME'] ?? ''
            ],
            'cpu' => [
                'cpuFamily' => $cpuInfo['cpu family'],
                'modelName' => $cpuInfo['model name'],
                'cpuMHz' => $cpuInfo['cpu MHz'],
                'cacheSize' => $cpuInfo['cache size'],
                'cpuCores' => $cpuInfo['cpu cores']
            ],
            'mem' => [
                'MemTotal' => $memeInfo['MemTotal'],
                'MemFree' => $memeInfo['MemFree'],
                'Cached' => $memeInfo['Cached'],
                'SwapCached' => $memeInfo['SwapCached'],
                'SwapTotal' => $memeInfo['SwapTotal'],
                'SwapFree' => $memeInfo['SwapFree'],
                'Percpu' => $memeInfo['Percpu'],
            ],
            'disk' => $this->getDiskSpace(),
            'php_version' => phpversion(),
            'Zend' => zend_version(),
        ];
    }

    protected function getInfo($configPath = ''): array
    {
        $cpuInfo = [];
        if (file_exists($configPath)) {
            $info = explode(PHP_EOL, trim(file_get_contents($configPath)));
            foreach ($info as $key) {
                $data = explode(':', $key);
                if (count($data) > 1) {
                    [$name, $value] = $data;
                    $cpuInfo[trim($name)] = trim($value);
                }
            }
        }
        return $cpuInfo;
    }
    
    public function getCpuInfo($path = '/proc/cpuinfo'): array {
        return $this->getInfo($path);
    }

    public function getMemInfo($path = '/proc/meminfo'): array
    {
        return $this->getInfo($path);
    }

    public function getDiskSpace(): array
    {
        return [
            'total' => $this->readable_size(disk_total_space('.')),
            'free' => $this->readable_size(disk_free_space('.')),
        ];
    }

    public function readable_size($length): string {
        $units = ['B', 'kB', 'MB', 'GB', 'TB'];
        foreach ($units as $unit) {
            if($length > 1024)
                $length = round($length/1024, 1);
            else break;
        }
        return $length.' '.$unit;
    }

    public function get_extension_loaded(array $extensions): array
    {
        $not_ext = [];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $not_ext[] = $ext;
            }
        }
        return $not_ext;
    }

    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}