<?php


namespace iflow\initializer;

// 加载应用配置
use iflow\App;
use iflow\fileSystem\File;
use iflow\Utils\ArrayTools;

/**
 * Class Config
 * @package iflow\initializer
 * @property File $file
 */
class Config
{
    public App $app;
    public ArrayTools $config;
    protected File $file;

    public function initializer(App $app)
    {
        // 加载基本配置
        $this->app = $app;
        $this->config = new ArrayTools();
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
        if (str_contains($name, 'swoole.') && !swoole_success()) return [];
        return $this->set($name, loadConfigFile($file));
    }

    public function set(string $name, array $config = [])
    {
        if (count($config) === 0) {
            return $this->config -> offsetSet($name, $config);
        }

        return $this->config -> offsetSet(
            $name, isset($this->config[$name]) ? array_replace_recursive($this->config[$name], $config) : $config
        );
    }

    public function get(string $name = '', $default = []): array|string
    {

        return $this->config -> get($name, $default);
    }

    public function has(string $name) : bool
    {
        return $this->config -> offsetExists($name);
    }
}