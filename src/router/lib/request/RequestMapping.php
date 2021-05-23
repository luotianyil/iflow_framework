<?php


namespace iflow\router\lib\request;

#[\Attribute]
class RequestMapping
{
    protected string $method = "*";
    public function __construct(
        protected string $rule = '',
        protected string $ext = '*',
        protected array $parameter = [],
        protected array $options = []
    ){}

    /**
     * @return array
     */
    public function getMethod(): array
    {
        return explode('|', strtolower($this->method));
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return array
     */
    public function getExt(): array
    {
        return explode('|', $this->ext);
    }

    /**
     * @return array
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}