<?php


namespace iflow\initializer;

// 加载应用配置
use iflow\App;
use iflow\fileSystem\File;

/**
 * Class Config
 * @package iflow\initializer
 * @property File $file
 */
class Config
{
    public App $app;
    public array $config = [];
    protected File $file;

    public function initializer(App $app)
    {
        // 加载基本配置
        $this->app = $app;
        $this->file = app(File::class) -> initializer();
        $this->load($this->file -> fileList -> loadFileList($this->app->getConfigPath(), $this->app -> getConfigExt() ,true));
    }

    /**
     * @param array $configFile | 配置文件列表
     * @param string $configKey | 配置key
     */
    public function load($configFile = [], string $configKey = '')
    {
        foreach ($configFile as $key => $value) {
            if (is_array($value)) {
                $this->load($value, $configKey . '.' . $key);
            } elseif (is_string($value)) {
                if (file_exists($value)) {
                    $this->parse($value, trim($configKey . '.' .$key, '.'));
                }
            }
        }
    }

    public function parse(string $file, string $name)
    {
        $type   = pathinfo($file, PATHINFO_EXTENSION);
        $config = match ($type) {
            'php' => include $file,
            'ini' => parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [],
            'json' => json_decode(file_get_contents($file), true),
            'yaml' => function () use ($file) {
                if (function_exists('yaml_parse_file')) {
                    return yaml_parse_file($file);
                }
                return [];
            }
        };

        $config = is_numeric($config)?[] : $config;
        $this->set($name, is_object($config) ? $config() : $config);
    }

    public function set(string $name, array $config = [])
    {
        return $this->config[$name] = isset($this->config[$name]) ? array_replace_recursive($this->config[$name], $config) : $config;
    }

    public function get(string $name = '', $default = null)
    {
        if ($name === '') return $this->config;
        $keys = explode('@', $name);
        if (!$this->has($keys[0])) return [];
        // 返回全部
        if (empty($keys[1])) return $this->config[$keys[0]];
        $names = explode('.', $keys[1]);
        $info = [];
        if (count($names) <= 1) {
            foreach ($names as $val) {
                if (isset($this->config[$keys[0]][$val])) {
                    $info = $this->config[$keys[0]][$val];
                }
            }
        } else {
            return $this->getConfigValue($names, $this->config[$keys[0]]);
        }
        return $info;
    }

    protected function getConfigValue($names, array $config = [])
    {
        // 按.拆分成多维数组进行判断
        if (count($names) === 1) {
            return $config[array_shift($names)] ?: [];
        }
        $key = array_shift($names);
        return $this->getConfigValue($names, $config[$key]);
    }

    public function has(string $name) : bool
    {
        return empty($this->config[$name]) ? false : true;
    }

    public function saveConfigFile($config, $name, $path)
    {
        !is_dir($path) && mkdir($path, 0755, true);
        $file = $path. $name. '.php';
        $fileStream = fopen($file, "w+");
        fwrite($fileStream, serialize($config));
        return fclose($fileStream);
    }

    public function getConfigFile($name)
    {
        $file = $name. '.php';
        if (file_exists($file))
            return unserialize(file_get_contents($file));
        return [];
    }

    public function delConfigFile($path)
    {
        $file = $path. '.php';
        if (file_exists($file))
            return @unlink($file);
        return true;
    }
}