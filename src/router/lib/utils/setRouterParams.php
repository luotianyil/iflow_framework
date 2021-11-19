<?php


namespace iflow\router\lib\utils;


class setRouterParams
{
    // 路由方法
    protected \ReflectionMethod $method;

    // 解析的路由参数
    protected array $params = [];
    protected array $routerParams = [];

    /**
     * @param array $routerParams
     * @return static
     */
    public function setRouterParams(array $routerParams): static
    {
        $this->routerParams = $routerParams;
        return $this;
    }

    // 获取路由方法参数
    public function getRouterMethodParameter(\ReflectionMethod $method): array
    {
        $this->method = $method;
        $this->params = $this->nextParameter();
        return [
            $this->params,
            $this->routerParams
        ];
    }

    // 遍历方法参数
    public function nextParameter(): array
    {
        $parameters = $this->method -> getParameters();
        $parameter = [];

        foreach ($parameters as $param) {
            $type = app() -> getParameterType($param);
            $name = $param -> getName();
            $typeName = $type[0] ?? '';
            if ($typeName === 'mixed') {
                $parameter[$name] = [
                    'type' => $type,
                    'name' => $name,
                    'default' => $this->getParamDefault($param, ['mixed'])
                ];
                continue;
            }

            if (class_exists($typeName)) {
                if (empty($this->routerParams[$typeName])) {
                    $this->getClassParams($typeName);
                }
                $parameter[$name] = [
                    'type' => ['class'],
                    'class' => $typeName,
                    'name' => $name
                ];
                continue;
            }
            $parameter[$name] = [
                'type' => $type,
                'name' => $name,
                'default' => $this->getParamDefault($param, $type)
            ];
        }

        return $parameter;
    }

    /**
     * 如果是类参数则遍历 获取类参数
     * @param $className
     * @throws \ReflectionException
     */
    public function getClassParams($className)
    {
        // 反射实例化类
        $parametersType = new \ReflectionClass($className);
        $parametersTypeInstance = $parametersType -> newInstance();
        $this->routerParams[$className] = [];
        // 遍历 public 参数
        foreach ($parametersType -> getProperties() as $param) {
            $paramName = $param -> getName();
            $defaultValue = $parametersType -> getProperty($paramName);
            if ($defaultValue -> isPublic()) {
                $paramType = app() -> getParameterType($param);

                $params = [
                    'type' => $paramType,
                    'class' => $parametersTypeInstance::class,
                    'name' => $paramName,
                    'default' => $this -> getParamDefault($param, $paramType)
                ];

                if (class_exists($paramType[0])) {
                    $params['class'] = $paramType[0];
                    $params['type'] = ['class'];
                }

                if (class_exists($paramType[0]) && $paramType[0] !== $className) {
                    $this -> getClassParams($paramType[0]);
                }
                $this->routerParams[$className][$param -> getName()] = $params;
            }
        }
    }

    // 获取参数默认值
    public function getParamDefault(\ReflectionProperty|\ReflectionParameter $param, string|array $type): mixed
    {
        $isDefault = $param instanceof \ReflectionProperty?
            $param -> isDefault() : $param -> isDefaultValueAvailable();
        $default = $isDefault ? $param -> getDefaultValue() : '';
        if ($default !== '') return $default;
        $type = is_string($type) ? [$type] : $type;

        if (class_exists($type[0])) {
            return app() -> make($type[0], isNew: true);
        }
        $default = match ($type[0]) {
            'mixed', 'string' => '',
            'int', 'float' => 0.00,
            'array' => [],
            'bool' => true
        };
        return $default;
    }
}