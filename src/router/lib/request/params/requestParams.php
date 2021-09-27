<?php


namespace iflow\router\lib\request\params;


use iflow\annotation\lib\abstracts\annotationAbstract;
use iflow\router\exception\RouterParamsException;

#[\Attribute]
class requestParams extends annotationAbstract
{

    protected \ReflectionParameter|\ReflectionProperty $ref;

    public function __construct(protected bool $required = true, protected string $message = "") {}

    /**
     * 验证参数可否为空
     * @param \ReflectionParameter|\ReflectionProperty $ref
     * @param $object
     * @param array $args
     * @throws RouterParamsException
     */
    public function handle(\ReflectionParameter|\ReflectionProperty $ref, $object, array &$args = [])
    {
        try {

            $this->ref = $ref;

            $value = $this->getValue($ref, $object, $args);
            $requestParams = request() -> params($ref -> getName());

            if ($this->required && (is_null($value) || (!isset($value) || $value === ""))) $this -> exception();
            $paramType = app() -> getParameterType($ref);
            $default = $this->getRefDefaultValue($ref);

            $valid = match (!in_array('string', $paramType) && $requestParams != $value) {
                true => function () use ($paramType, $value, $default) {
                    // 检测是否为数值
                    if (in_array('int', $paramType) || in_array('float', $paramType)) {
                        $this->validParams($value, $default, 0.00);
                    }

                    // 检测是否为bool
                    if (in_array('bool', $paramType)) {
                        $this->validParams($value, $default, true);
                    }
                },
                default => fn() => true
            };

            $valid();
        } catch (\Exception $exception) {
            $this -> exception();
        }
    }

    protected function validParams($value, $default, $paramsDefault)
    {
        if ($value !== $default && $value === $paramsDefault) {
            $this -> exception();
        }
    }

    public function exception()
    {
        throw new RouterParamsException(
            $this -> message ?: "QueryParamsName: ". $this->ref -> getName(). " required"
        );
    }

}