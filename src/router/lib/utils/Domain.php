<?php


namespace iflow\router\lib\utils;

#[\Attribute]
class Domain
{
    // 路由绑定域名
    public function __construct(
        protected array|string $domain = '*'
    ) {}


    /**
     * @return array
     */
    public function getDomain(): array
    {
        if (is_string($this->domain)) {
            $this->domain = explode('|', $this->domain);
        }
        return $this->domain;
    }

}