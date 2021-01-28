<?php


namespace iflow;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class Container implements ContainerInterface
{

    // 实例化容器管理
    public ?\WeakMap $containers = null;

    // 容器 唯一标值
    public array $bind = [];

    protected static $instance = null;

    /**
     * 创建实例
     * @param string $class 实例化类名
     * @param array $vars 实例化参数
     * @param bool $isNew 是否重新实例化
     * @return mixed
     */
    public function make(string $class, $vars = [], bool $isNew = false) :mixed
    {

        if ($isNew) $this->delete($class);

        $this->containers = $this->containers ?: new \WeakMap();

        // 判断容器是否存在 该对象
        if ($this->has($class)) return $this->get($class);
        // 不存在 实例化
        $this->bind[$class] = new \stdClass();
        return $this->invokeClass($class, $vars);
    }

    // 实例化 对象 返回
    public function invokeClass(string $class, $vars)
    {
        try {
            $ref = new \ReflectionClass($class);
            if ($ref -> hasMethod('__make')) {
                $method = $ref -> getMethod('__make');
                if ($method -> isPublic() && $method -> isStatic()) {
                    $vars = $this->bindParameters($method, $vars);
                    $method -> invokeArgs(null, $vars);
                }
            }
            $constructor = $ref -> getConstructor();
            $vars = $constructor ? $this->bindParameters($constructor, $vars) : [];
            $object = $ref -> newInstanceArgs($vars);

            if (!$this->has($class)) $this->bind[$class] = new \stdClass();

            $this->containers[$this->bind[$class]] = $object;
            return $object;
        } catch (\ReflectionException) {
            throw new \Error('Class not exists: ' . $class);
        }
    }

    // 执行闭包
    public function invokeFunction($methods, array $vars = [])
    {
        try {
            $ref = new \ReflectionFunction($methods);
            $args = $this->bindParameters($ref, $vars);
            return $methods(...$args);
        } catch (\ReflectionException) {
            throw new \Error("function not exists: ${methods}");
        }
    }

    public function invokeMethod($methods, array $vars = [])
    {
        [$class, $methods] = is_array($methods) ? $methods : explode('::', $methods);
        try {
            $ref = new \ReflectionMethod($class, $methods);
            $args = $this->bindParameters($ref, $vars);
            return $ref->invokeArgs(is_object($class) ? $class : null, $args);
        } catch (\ReflectionException $e) {
            throw new \Error("function not exists: ${methods}");
        }
    }

    // 反射执行方法
    public function invoke(array|string $callable, array $vars = [])
    {
        if ($callable instanceof \Closure) {
            return $this->invokeFunction($callable, $vars);
        } elseif (is_string($callable) && !str_contains($callable, '::')) {
            return $this->invokeFunction($callable, $vars);
        } else {
            return $this->invokeMethod($callable, $vars);
        }
    }

    // 绑定参数
    public function bindParameters(ReflectionFunctionAbstract $methods, $vars) : array
    {
        if (!$vars && $methods -> getNumberOfParameters() === 0) return [];
        $parameters = $methods -> getParameters();

        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter -> getName();
            $class = $parameter -> getType();
            if ($class instanceof ReflectionNamedType) {
                if (class_exists($class -> getName())) {
                    $args[] = $this->getObjectParam($class -> getName(), $vars);
                } elseif (1 == $type && !empty($vars)) {
                    $args[] = array_shift($vars);
                } else if ($parameter -> isDefaultValueAvailable()) {
                    $args[] = $parameter -> getDefaultValue();
                } else {
                    throw new \Error('method param miss:' . $name);
                }
            }
        }
        return $args;
    }

    // 获取方法参数 返回实例化
    public function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
        } else {
            $result = $this->make($className);
        }
        return $result;
    }

    // 实例化后回调
    public function invokeAfter($class, $object)
    {}

    // 获取当前容器
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        if (static::$instance instanceof \Closure) {
            return (new static())();
        }

        return static::$instance;
    }

    /**
     * 绑定一个类实例到容器
     * @access public
     * @param string $abstract 类名或者标识
     * @param object $instance 类的实例
     * @return $this
     */
    public function instance(string $abstract, object $instance)
    {
        $this->containers = $this->containers ?: new \WeakMap();
        if (!$this->has($abstract)) $this->bind[$abstract] = new \stdClass();
        $this->containers[$this->bind[$abstract]] = $instance;
        return $this;
    }

    // 设置当前容器
    public static function setInstance($instance) :void
    {
        static::$instance = $instance;
    }

    // 删除容器 内对象
    public function delete($class) : void
    {
        if ($this->has($class)) {
            $this->containers -> offsetUnset($this->bind[$class]);
            unset($this->bind[$class]);
        }
    }

    /**
     * 获取 容器 内对象
     * @param string $id
     * @return mixed
     */
    public function get($id): object
    {
        // TODO: Implement get() method.
        if ($this->has($id)) {
            return $this->containers -> offsetGet($this->bind[$id]);
        }
        throw new \Error('class not exists: '. $id);
    }

    /**
     * 验证容器内对象是否存在
     * @param string $id 类名
     * @return bool
     */
    public function has($id): bool
    {
        // TODO: Implement has() method.
        return !empty($this->bind[$id]);
    }
}