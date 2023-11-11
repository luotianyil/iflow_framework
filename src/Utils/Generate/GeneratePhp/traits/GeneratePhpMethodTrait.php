<?php

namespace iflow\Utils\Generate\GeneratePhp\traits;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Utils\Generate\GeneratePhp\GeneratePhpClassException;

trait GeneratePhpMethodTrait {

    /**
     * 读取PHP 模板代码
     * @param string $path
     * @return string
     * @throws GeneratePhpClassException
     */
    protected function readPhpTemplate(string $path = ''): string {

        $path = $path ?: $this -> phpTemplateDefaultPath . "/../php_class.tpl";

        if (!file_exists($path)) throw new GeneratePhpClassException('PHP_CLASS TEMPLATE FILE DOSE NOT EXISTS');

        return file_get_contents($path);
    }

    /**
     * 获取需要生成的方法列表
     * @return array
     * @throws \ReflectionException
     */
    protected function getMethods(): array {

        if (empty($this -> implements) && isset($this -> methods)) return $this -> methods;

        return $this->getImplementsMethod($this -> getImplements());
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    protected function generateMethodPhpCode(): string {

        $methodPhpCode = [];

        foreach ($this->getMethods() as $method) {

            $methodCode = call_user_func($this -> method, $method, $this);

            if (!is_string($methodCode)) continue;

            $methodPhpCode[] = sprintf("%s%s function %s(%s): %s { \n        %s \n    }\n",
                $method['descriptor'],
                $method['static'] ? ' static' : '',
                $method['methodName'],
                implode(', ', array_map(fn ($type) => $type['type_str'], $method['parameters'])),
                $method['returnType'],
                $methodCode
            );
        }

        return implode("\n", $methodPhpCode);
    }

    /**
     * 通过接口获取方法
     * @param array $implements
     * @return array
     * @throws \ReflectionException|InvokeClassException
     */
    protected function getImplementsMethod(array $implements): array {

        $implementMethods = [];

        $implements = $implements ?: $this -> implements;

        if (empty($implements)) return [];

        foreach ($implements as $implement) {
            $implement = new \ReflectionClass($implement);

            $methods = $this -> filterImplementParentMethod
                ? app() -> getMethodFilterParent($implement)
                : $implement -> getMethods();

            foreach ($methods as $method) {

                $implementMethods[] = [
                    'methodName' => $method -> getName(),
                    'descriptor' => $this->getMethodDescriptor($method),
                    'static' => $method -> isStatic(),
                    'parameters' => $this -> getMethodParameters($method),
                    'returnType' => app()
                        -> parameterTypeToStr(
                            type: app() -> getParameterType($method, 'getReturnType')
                        )
                ];
            }
        }

        return $implementMethods;
    }

    /**
     * 获取方法标识
     * @param \ReflectionMethod $method
     * @return string
     */
    public function getMethodDescriptor(\ReflectionMethod $method): string {

        if ($method -> isPrivate()) return 'private';

        if ($method -> isPublic()) return 'public';

        if ($method -> isProtected()) return 'protected';

        return '';
    }

    /**
     * 获取方法参数
     * @param \ReflectionMethod $method
     * @return array
     * @throws InvokeClassException
     */
    public function getMethodParameters(\ReflectionMethod $method): array {

        $parametersType = [];

        foreach ($method -> getParameters() as $parameter) {

            $parameterType = app() -> getParameterType($parameter);

            $parametersType[] = [
                'name' => $parameter -> getName(),
                'type' => $parameterType,
                'type_str' => app() -> parameterTypeToStr(
                    $parameter -> getName(),
                    $parameterType
                )
            ];
        }

        return $parametersType;
    }

}
