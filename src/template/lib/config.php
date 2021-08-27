<?php


namespace iflow\template\lib;


use iflow\template\lib\document\Tag\Db;
use iflow\template\lib\document\Tag\echoTag;
use iflow\template\lib\document\Tag\functionTag;
use iflow\template\lib\document\Tag\includeTag;
use iflow\template\lib\document\Tag\Literal;
use iflow\template\lib\document\Tag\PHPScript;

class config
{

    protected array $defaultConfig = [
        'tags' => [
            'db' => [
                'class' => Db::class
            ],
            'phpscript' => [
                'class' => PHPScript::class
            ],
            'echo' => [
                'class' => echoTag::class
            ],
            'function' => [
                'class' => functionTag::class
            ],
            'literal' => [
                'class' => Literal::class
            ],
            'include' => [
                'class' => includeTag::class
            ]
        ]
    ];

    public function __construct(
        public array $config = []
    ) {
        $this->config = array_merge_recursive($this->defaultConfig, $this->config);
    }

    /**
     * 设置配置
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function setConfig(string $name, mixed $value): bool
    {
        $this->config[$name] = $value;
        return true;
    }

    /**
     * 是否开启缓存
     * @return bool
     */
    public function getCacheEnable(): bool
    {
        return $this->config['cache_enable'] ?? false;
    }

    /**
     * 获取缓存地址
     * @return string
     */
    public function getStorePath(): string
    {
        return $this->config["store_path"] ?? "";
    }

    /**
     * 获取视图根目录
     * @return string
     */
    public function getViewRootPath(): string
    {
        return $this->config['view_root_path'] ?? '';
    }

    /**
     * 获取视图文件后缀
     * @return string
     */
    public function viewSuffix(): string
    {
        return $this->config['view_suffix'] ?? '';
    }

    /**
     * 获取 自定义 TAG
     * @param string $name
     * @param array $default
     * @return array
     */
    public function getTagByName(string $name, array $default = []): array
    {
        return $this->config['tags'][$name] ?? $default;
    }

    /**
     * 获取原样config配置
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }
}