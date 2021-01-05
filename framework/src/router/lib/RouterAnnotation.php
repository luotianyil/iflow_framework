<?php


namespace iflow\router\lib;

// 扫描全局类

use iflow\App;
use iflow\fileSystem\File;
use iflow\initializer\annotationInitializer;

/**
 * Class RouterAnnotation
 * @package iflow\router\lib
 * @property File $file
 */

#[\Attribute]
class RouterAnnotation
{
    // 搜索类 命名空间
    public array $useScanNamespace = [];
    // App 主应用
    protected App $app;

    // 所找到的类
    protected array $useClass = [];


    /**
     * RouterAnnotation constructor.
     * @param array $namespace 扫描空间
     */
    public function __construct(array $namespace = [])
    {
        $this->useScanNamespace = $namespace;
        $this->file = app(File::class) -> initializer();
    }

    // 扫描包
    public function scanRouter()
    {
        foreach ($this->useScanNamespace as $key) {
            $this->useClass[$key] = $this->file -> fileList -> loadFileList($this->app -> getRootPath() . $key, '.php', true);
        }
        $this->loadRouter();
    }

    // 加载路由
    public function loadRouter($useClass = [], $nameSpace = '')
    {
        $useClass = $useClass ?: $this->useClass;
        foreach ($useClass as $key => $value) {
            if (is_array($value)) {
               if (sizeof($value) > 0) $this->loadRouter($value, $nameSpace.'\\'.$key);
            } elseif (file_exists($value)) {
                $class = str_replace('.php', '', str_replace($this->app -> getRootPath(), '', $value));
                $class = str_replace('/', '\\', $class);
                app(annotationInitializer::class) -> loadAnnotations(new \ReflectionClass($class) ?: throw new \Error('初始化失败'));
            }
        }
    }


    public function __make(App $app)
    {
        $this->app = $app;
        $this->scanRouter();
    }
}