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

    protected array $classes = [];

    public function __construct(array $namespace = [], protected array $config = []) {
        $this->useScanNamespace = $namespace;
        $this->config = $this->config ?: config('annotation');
        $this->file = app(File::class) -> initializer();
    }

    /**
     * 扫描全局文件
     * @throws \ReflectionException
     */
    protected function scanPacks(): bool {
        // 验证缓存数据
        if ($this->cacheEnable() && !empty($this->getCache())) {
            $this->loadPackClass($this -> classes);
            return true;
        }

        // 重新加载数据
        foreach ($this->useScanNamespace as $key) {
            $this->useClass[$key] = $this->file -> fileList -> loadFileList($this->app -> getRootPath() . $key, '.php', true);
        }
        $this->loadPackClass($this->useClass);
        if ($this->cacheEnable()) $this->saveCachePackClass();
        return true;
    }

    /**
     * 加载全部可用类
     * @param array $useClass
     * @param string $nameSpace
     * @throws \ReflectionException
     */
    protected function loadPackClass(array $useClass = [], string $nameSpace = '') {
        foreach ($useClass as $key => $value) {
            if (is_array($value)) {
                if (sizeof($value) > 0) $this->loadPackClass($value, $nameSpace.'\\'.$key);
            } elseif (file_exists($value) && !in_array($value, $this->useClass)) {
                $class = str_replace('.php', '', str_replace($this->app -> getRootPath(), '', $value));
                $class = str_replace('/', '\\', $class);
                if (class_exists($class)) {
                    $this->classes[] = $value;
                    app(annotationInitializer::class)->loadAnnotations(new \ReflectionClass($class));
                }
            }
        }
    }

    /**
     * 验证是否开启缓存
     * @return bool
     */
    protected function cacheEnable(): bool {
        return $this->config['cache_enable'] ?? false;
    }

    /**
     * 获取缓存数据
     * @return array
     */
    protected function getCache(): array {
        $cachePath = $this->config['cache_path'];
        $path = str_replace("\\", '/', $cachePath);
        return $this->classes = $this->cacheEnable() && file_exists($path) ? unserialize(file_get_contents($path) ?: '') : [];
    }

    /**
     * 储存缓存文件
     * @return bool|int
     */
    protected function saveCachePackClass(): bool|int {
        $cachePath = $this->config['cache_path'];
        $path = str_replace("\\", '/', $cachePath);
        !is_dir(dirname($path)) && mkdir(dirname($path), recursive: true);
        $this->classes = array_unique($this->classes);
        $content = serialize($this->classes);
        return file_put_contents($path, $content);
    }

    /**
     * 程序初始化入口
     * @param App $app
     * @return void
     * @throws \ReflectionException
     */
    public function __make(App $app) {
        $this->app = $app;
        $this->scanPacks();
    }
}