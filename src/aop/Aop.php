<?php


namespace iflow\aop;


use iflow\aop\lib\Ast;
use iflow\App;
use iflow\exception\lib\HttpException;
use iflow\pipeline\pipeline;

class Aop
{
    // 用户定义切面
    private array $aspects = [];

    // AOP 配置
    private array $config;

    // 管道
    protected pipeline $pipeline;

    protected App $app;

    // 执行切面返回数据
    private mixed $response = "";

    public function __construct()
    {
        $this->config = config('aop');
    }

    /**
     * 生成拦截类
     * @param string $class 执行类
     * @param string $method 执行方法
     * @param mixed ...$args
     * @return bool|Aop
     * @throws \ReflectionException
     */
    public function process(string $class, string $method, ...$args): bool|static
    {
        // 统计切面， 以批量执行
        $aspects = [];
        if (count($this->aspects) === 0) return false;
        foreach ($this->aspects as $aspect => $classes) {
            foreach ($classes as $className) {
                $list = explode("::", $className);
                $action = "";
                if (count($list) > 1) [$className, $action] = $list;
                if ($className !== $class) continue;
                if (!$this->MethodsProxy($method, $action) && count($list) > 1) continue;

                $hashClass = "_" . sha1($aspect . $class);

                // 生成代理类
                if (!$this->CacheExists($hashClass)) {
                    $ast = (new Ast()) -> proxy($class, aspectClass: $aspect);
                    if ($ast === "") throw new HttpException(502, "proxyClass {$class} not exists");
                    $this->saveCache($hashClass, $ast);
                }
                include_once $this->config['cache_path']. DIRECTORY_SEPARATOR . $hashClass . ".php";
                $aspects[] = (new \ReflectionClass(substr($className, 0, strrpos($className, "\\"))."\\$hashClass")) -> newInstance();
                break;
            }
        }
        // 无切面情况下 返回false 执行 原生方法
        return count($aspects) > 0 ? $this->throughAop($aspects, $method, $args) : false;
    }

    /**
     * 追加切面
     * @param string $class
     * @param array $aspectArray
     */
    public function addAspect(string $class, array $aspectArray)
    {
        $this->aspects[$class] = $aspectArray;
    }

    /**
     * 存储 代理缓存
     * @param string $class
     * @param string $content
     * @return bool|int
     */
    private function saveCache(string $class, string $content): bool|int
    {
        $path = $this->config['cache_path']. DIRECTORY_SEPARATOR . $class. ".php";
        !is_dir(dirname($path)) && mkdir(dirname($path));
        return file_put_contents($path, $content);
    }

    /**
     * 验证缓存文件是否存在 / 是否开启缓存
     * @param string $class
     * @return bool
     */
    private function CacheExists(string $class = ""): bool
    {
        $file_path = $this->config['cache_path']. DIRECTORY_SEPARATOR . $class. ".php";
        if ($this->config['cache_enable'])
            return file_exists($file_path);

        if (file_exists($file_path)) unlink($file_path);
        return false;
    }

    /**
     * 验证方法是否需要切面
     * @param string $method
     * @param string $action
     * @return bool
     */
    private function MethodsProxy(string $method = "", string $action = ""): bool {
        if (substr($action, -1, 1) === '*') {
            $prefix = substr($action, 0, strlen($action) - 1);
            return str_starts_with($method, $prefix);
        }
        return $method === $action;
    }

    /**
     * 将数据写入管道
     * @param array $aspects
     * @param string $action
     * @param array $args
     * @return $this
     */
    private function throughAop(array $aspects, string $action, array $args): static {
        // 追加管道
        $this->pipeline = new pipeline();
        $this->pipeline -> through(array_map(function ($aspect) use ($args, $action) {
            return function ($app, $next) use ($aspect, $args, $action) {
                // 通过容器反射执行
                $callback = app() -> invokeMethod([$aspect, $action], $args);
                if ($callback !== true) {
                    $this->response = $callback;
                    throw new \Exception('Aop returns not true');
                }
                $next($app);
            };
        }, $aspects));
        return $this;
    }

    /**
     * 执行管道
     * @return mixed
     */
    public function then(): mixed
    {
        try {
            $this->pipeline -> process(app());
            return true;
        } catch (\Exception $exception) {
            return $this->response ?: $exception -> getMessage();
        }
    }
}
