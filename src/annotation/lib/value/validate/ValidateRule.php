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

        $ref -> setValue(
            $object, $ref -> getDefaultValue() ?: $this->defaultValue
        );

        $this->defaultValue = $this->toArray($this->defaultValue, $name);

        try {
            validate($this->rule, $this->defaultValue, $this->errMsg);
        } catch (\Exception $exception) {
            throw (new valueException()) -> setError(message() -> parameter_error($exception -> getMessage()));
        }
    }

    private function toArray($value, $name): array {
        return !is_array($value) ? [
            $name => $value
        ]: $value;
    }
}