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
        $this->containers = $this->containers ?: new \WeakMap();

        // 判断容器是否存在 该对象
        if ($this->has($class)) return $this->containers -> offsetGet($this->bind[$class]);
        // 不存在 实例化
        $this->bind[$class] = new \stdClass();
        return $this->invokeClass($class, $vars);
    }

    // 实例化 对象 返回
    public function invokeClass(string $class, $vars)
    {
        $ref = new \ReflectionClass($class) ?: throw new \Error('Class not exists: '.$class);
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
        $this->containers[$this->bind[$class]] = $object;
        return $object;
    }

    // 绑定参数
    public function bindParameters(ReflectionFunctionAbstract $methods, $vars) : array
    {
        if (!$vars || $methods -> getNumberOfParameters() === 0) return [];
        $parameters = $methods -> getParameters();

        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter -> getName();
            $class = $parameter -> getType();
            assert($class instanceof ReflectionNamedType);
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
        return $args;
    }

    // 获取方法参数 返回实例化
    public function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($vars instanceof $className) {
            $result = $value;
        } else {
            $result = $this->make($className);
        }

        array_shift($vars);
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

    // 设置当前容器
    public static function setInstance($instance) :void
    {
        static::$instance = $instance;
    }

    // 删除容器 内对象
    public function delete($class) : void
    {
        unset($this->containers[$this->bind[$class]]);
        unset($this->bind[$class]);
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