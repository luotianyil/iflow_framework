<?php


namespace iflow\auth\Authorize\Jwt\Tools;


class Config
{

    // jwt 头部信息
    protected array $header = [
        'alg' => 'HS256',
        'type' => 'JWT'
    ];

    protected string $key = "";

    // jwt签名
    protected string $signature;

    protected array $payload = [
        // 签发者
        'iss' => '',
        // 签发时间
        'iat' => '',
        // 过期时间
        'exp' => '',
        // 生效时间
        'nbf' => '',
        // 订阅用户
        'sub' => '',
        // token 唯一标识
        'jti' => '',
        // 自定义数据
        'payload' => ''
    ];

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param array $header
     * @return static
     */
    public function setHeader(array $header): static
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param array $payload
     * @return static
     */
    public function setPayload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @param string $signature
     * @return static
     */
    public function setSignature(string $signature): static
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @param string $key
     * @return config
     */
    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

}