<?php

namespace iflow\template\Adapter\Regx;

use iflow\template\Adapter\Regx\implement\Tag\DbTag;
use iflow\template\Adapter\Regx\implement\Tag\ForeachTag;
use iflow\template\Adapter\Regx\implement\Tag\IfTag;
use iflow\template\Adapter\Regx\implement\Tag\IncludeTag;
use iflow\template\Adapter\Regx\implement\Tag\PhpScriptTag;
use iflow\template\config\Config as DConfig;

class Config extends DConfig {

    protected array $defaultConfig = [
        'tags' => [
            'foreach' => [
                'class' => ForeachTag::class
            ],
            'db' => [
                'class' => DbTag::class
            ],
            'include' => [
                'class' => IncludeTag::class
            ],
            'phpscript' => [
                'class' => PhpScriptTag::class
            ],
            // 条件指令
            'if' => [
                'class' => IfTag::class
            ],
            'elseif' => [
                'class' => IfTag::class
            ],
            'else' => [
                'class' => IfTag::class
            ],
        ],
        // 自定义指令
        'instruction' => [],
        // 文件尾缀
        'view_suffix' => 'html',
        // 是否开启缓存
        'cache_enable' => true,
        // 缓存文件前缀
        'cache_prefix' => 'template',
        // 缓存地址
        'store_path' => '',
        // 视图目录
        'view_root_path' => '',
        // 缓存时间
        'cache_time' => 0
    ];


    /**
     * 获取标签列表
     * @return array
     */
    public function getTagKey(): array {
        return array_keys($this->defaultConfig['tags']);
    }

}