<?php


namespace iflow\annotation\lib\value\validate;


use iflow\annotation\lib\value\Exception\valueException;

#[\Attribute]
class ValidateRule
{
    public function __construct(
        protected string|array $rule = [],
        protected string|array $errMsg = [],
        protected mixed $defaultValue = ""
    ) {}

    public function handle(\ReflectionProperty $ref, $object)
    {
        $name = $ref -> getName();

        $this->rule = $this->toArray($this->rule, $name);
        $this->errMsg = $this->toArray($this->errMsg, $name);

        $defaultValue = $ref -> getDefaultValue() ?: $this->defaultValue;

        // 获取验证参数
        try {
            $defaultValue = $ref -> getValue($object);
        } catch (\Error) {}

        try {
            // 设置验证参数
            $ref -> setValue($object, $defaultValue);
        } catch (\Error) {
            throw new valueException(message() -> parameter_error(
                $this -> errMsg ? $this -> errMsg[0] : '参数异常 请重试'
            ));
        }

        $this->defaultValue = $this->toArray($ref->getValue($object), $name);

        try {
            validate($this->rule, $this->defaultValue, $this->errMsg);
        } catch (\Exception $exception) {
            throw new valueException(
                message() -> parameter_error($exception -> getMessage())
            );
        }
    }

    private function toArray($value, $name): array {
        return !is_array($value) ? [
            $name => $value
        ]: $value;
    }
}