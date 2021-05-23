<?php


namespace iflow\annotation;


use iflow\App;
use iflow\fileSystem\File;
use iflow\initializer\annotationInitializer;

/**
 * 全局注解入口类
 * Class Annotation
 * @package iflow\annotation
 */
#[\Attribute]
class Annotation
{

    // 搜索类 命名空间
    public array $useScanNamespace = [];
    // App 主应用
    protected App $app;
    // 文件功能类
    protected File $file;

    // 所找到的类
    protected array $useClass = [];

    public function __construct(array $namespace = [])
    {
        $this->useScanNamespace = $namespace;
        $this->file = app(File::class) -> initializer();
    }

    /**
     * 扫描全局文件
     * @throws \ReflectionException
     */
    protected function scanPacks()
    {
        foreach ($this->useScanNamespace as $key) {
            $this->useClass[$key] = $this->file -> fileList -> loadFileList($this->app -> getRootPath() . $key, '.php', true);
        }
        $this->loadPackClass($this->useClass);
    }

    /**
     * 加载全部可用类
     * @param array $useClass
     * @param string $nameSpace
     * @throws \ReflectionException
     */
    protected function loadPackClass($useClass = [], $nameSpace = '')
    {
        foreach ($useClass as $key => $value) {
            if (is_array($value)) {
                if (sizeof($value) > 0) $this->loadPackClass($value, $nameSpace.'\\'.$key);
            } elseif (file_exists($value) && !in_array($value, $this->useClass)) {
                $this->useClass[] = $value;
                $class = str_replace('.php', '', str_replace($this->app -> getRootPath(), '', $value));
                $class = str_replace('/', '\\', $class);
                if (class_exists($class)) app(annotationInitializer::class)->loadAnnotations(new \ReflectionClass($class));
            }
        }
    }

    public function __make(App $app)
    {
        $this->app = $app;
        try {
            $this->scanPacks();
        } catch (\ReflectionException $e) {
            logs('error', $e -> getMessage());
        }
    }
}