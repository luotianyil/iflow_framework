<?php


namespace iflow;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use Reflector;

class Container implements ContainerInterface
{

    // 实例化容器管理
    public ?\WeakMap $containers = null;

    // 容器 唯一标值
    public array $bind = [];

    protected static ?Container $instance = null;

    /**
     * 创建实例
     * @param string $class 实例化类名
     * @param array $vars 实例化参数
     * @param bool $isNew 是否重新实例化
     * @return mixed
     */
    public function make(string $class, array $vars = [], bool $isNew = false) :mixed {

        if ($isNew) $this->delete($class);
        $this->containers = $this->containers ?: new \WeakMap();

        // 判断容器是否存在 该对象
        if ($this->has($class)) return $this->get($class);
        // 不存在 实例化
        $this->bind[$class] = new \stdClass();
        return $this->invokeClass($class, $vars);
    }

    /**
     * 实例化 对象 返回
     * @param string $class
     * @param $vars
     * @return object
     */
    public function invokeClass(string $class, $vars): object
    {
        $class = str_replace('\\\\', '\\', $class);
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

            // 实例化后运行注解
            $object = $ref -> newInstanceArgs($vars);
            $this->runAttributes($ref, $ref, $object);
            if (!$this->has($class)) $this->bind[$class] = new \stdClass();

            $this->containers[$this->bind[$class]] = $object;
            return $object;
        } catch (\ReflectionException $exception) {
            throw new \Error('Class not exists: ' . $class . $exception -> getMessage());
        }
    }

    /**
     * 执行闭包
     * @param $methods
     * @param array $vars
     * @return mixed
     */
    public function invokeFunction($methods, array $vars = []): mixed
    {
        try {
            $ref = new \ReflectionFunction($methods);
            $args = $this->bindParameters($ref, $vars);
            return $methods(...$args);
        } catch (\ReflectionException) {
            throw new \Error("function not exists: ${methods}");
        }
    }

    /**
     * 执行方法
     * @param $methods
     * @param array $vars
     * @return mixed
     */
    public function invokeMethod($methods, array $vars = []): mixed
    {
        [$class, $methods] = is_array($methods) ? $methods : explode('::', $methods);
        try {
            $ref = new \ReflectionMethod($class, $methods);
            $args = $this->bindParameters($ref, $vars);
            // 执行方法参数注解
            array_map(function ($parameter) use ($class, &$args) {
                $this -> runAttributes($parameter, $parameter, $class, $args);
            }, $ref -> getParameters());

            return $ref->invokeArgs(is_object($class) ? $class : null, $args);
        } catch (\ReflectionException) {
            throw new \Error("function not exists: ${methods}");
        }
    }

    /**
     * 反射执行方法
     * @param array|string $callable
     * @param array $vars
     * @return mixed
     */
    public function invoke(array|string $callable, array $vars = []): mixed
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
            $types = $this->getParameterType($parameter);
            if (count($types) > 0) {
                if (class_exists($types[0])) {
                    $args[] = $this->getObjectParam($types[0], $vars);
                }
                elseif (1 == $type && !empty($vars)) {
                    $args[] = array_shift($vars);
                }
                elseif ($parameter -> isDefaultValueAvailable()) {
                    $args[] = $parameter -> getDefaultValue();
                } else {
                    throw new \Error('method '. $methods -> getName() .' param miss:' . $name);
                }
            }
        }
        return $args;
    }

    // 获取方法参数 返回实例化
    public function getObjectParam(string $className, array &$vars)
    {
        $value = array_shift($vars);
        if ($value instanceof $className) {
            $result = $value;
        } else {
            $result = $this->make($className);
        }
        return $result;
    }

    // 实例化后回调
    public function invokeAfter($class, $object) {}

    // 获取当前容器
    public static function getInstance(): static
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
    public function instance(string $abstract, object $instance): static
    {
        $this->containers = $this->containers ?: new \WeakMap();
        if (!$this->has($abstract)) $this->bind[$abstract] = new \stdClass();
        $this->containers[$this->bind[$abstract]] = $instance;
        return $this;
    }

    // 设置当前容器
    public static function setInstance($instance) :void {
        static::$instance = $instance;
    }

    // 删除容器 内对象
    public function delete($class) : void {
        if (!empty($this->bind[$class])) {
            $this->containers -> offsetUnset($this->bind[$class]);
            unset($this->bind[$class]);
        }
    }

    /**
     * 获取 容器 内对象
     * @param string $id
     * @return mixed
     */
    public function get(string $id): object
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
    public function has(string $id): bool
    {
        // TODO: Implement has() method.
        if (empty($this->bind[$id]) || !$this->containers -> offsetExists($this->bind[$id])) {
            $this->delete($id);
            return false;
        }
        return true;
    }

    /**
     * 反射获取 变量类型 支持 ReflectionUnionType
     * @param \ReflectionProperty|\ReflectionParameter $property
     * @return array
     */
    public function getParameterType(\ReflectionProperty|\ReflectionParameter $property): array
    {
        $type = $property -> getType();
        $types = [];
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type -> getTypes() as $t) {
                $types[] = $t -> getName();
            }
        } else if ($type instanceof ReflectionNamedType) {
            $types[] = $type -> getName();
        }
        return $types ?: ['mixed'];
    }

    /**
     * 获取注解并执行
     * @param Reflector $ref
     * @param $parameter
     * @param $class
     * @param mixed ...$args
     */
    public function runAttributes(Reflector $ref, $parameter = [], $class = "", mixed &$args = []) {
        array_map(function ($attr) use ($parameter, $class, &$args) {
            $attrObject = $attr -> newInstance();
            return method_exists($attrObject, 'handle')
                ? $attrObject -> handle($parameter, $class, $args) : null;
        }, $ref -> getAttributes());
    }
}