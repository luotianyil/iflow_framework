<?php


namespace iflow\initializer;

use iflow\App;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\fileSystem\File;
use iflow\Helper\Arr\Arr;

/**
 * 加载应用配置
 * Class Config
 * @package iflow\initializer
 * @property File $file
 */
class Config {

    public App $app;

    #[Inject]
    public Arr $config;

    protected File $file;

    protected array $configFileExt = [ '.php', '.json', '.ini', '.yaml', '.env' ];

    public function initializer(App $app) {
        // 加载基本配置
        $this->app = $app;
        $this->config = new Arr();
        $this->file = $this -> app -> make(File::class) -> initializer();
        $this->load($this->file -> fileList -> loadFileList($this->app->getConfigPath(), $this -> configFileExt ,true));
    }

    /**
     * @param array $configFile | 配置文件列表
     * @param string $configKey | 配置key
     */
    public function load(array $configFile = [], string $configKey = '')
    {
        foreach ($configFile as $key => $value) {
            if (is_array($value)) {
                $this->load($value, $configKey . '-' . $key);
            } elseif (is_string($value)) {
                if (file_exists($value)) {
                    $this->parse($value, trim($configKey . '-' .$key, '-'));
                }
            }
        }
    }

    public function parse(string $file, string $name) {
        if (str_contains($name, 'swoole-') && !swoole_success()) return [];
        return $this->set($name, loadConfigFile($file));
    }

    public function set(string $name, array $config = [])
    {
        if (count($config) === 0) {
            return $this->config -> offsetSet($name, $config);
        }

        return $this->config -> offsetSet(
            $name, isset($this->config[$name]) ? array_replace_recursive($config, $this->config[$name]) : $config
        );
    }

    public function get(string $name = '', mixed $default = []): mixed {
        return $this->config -> get($name, $default);
    }

    public function has(string $name) : bool {
        return $this->config -> offsetExists($name);
    }
}