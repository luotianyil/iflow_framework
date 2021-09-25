<?php


namespace iflow\router\lib\utils;

#[\Attribute]
class Domain
{
    // 路由绑定域名
    public function __construct(
        protected array|string $domain = '*',
        protected array $args = []
    ) {}


    /**
     * 获取当前控制器绑定的域名
     * @return array
     */
    public function getDomain(): array
    {
        $called = valid_closure($this->domain, $this -> args);
        if ($called !== null) {
            $this->domain = $called();
        }

        if (is_string($this->domain)) {
            $this->domain = explode('|', $this->domain);
        }
        return $this->domain;
    }

}