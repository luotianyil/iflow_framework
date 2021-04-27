<?php


namespace iflow\annotation\lib\value\validate;


#[\Attribute]
class ValidateRule
{
    public function __construct(
        protected string $rule = "",
        protected array $errMsg = [],
        protected string $defaultValue = ""
    ) {}

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    /**
     * @return array
     */
    public function getErrMsg(): array
    {
        return $this->errMsg;
    }
}