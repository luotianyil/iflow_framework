<?php


namespace iflow\auth\lib\jwt;


use iflow\auth\lib\jwt\lib\base64;
use iflow\auth\lib\jwt\lib\config;
use iflow\auth\lib\jwt\lib\hmac;
use iflow\auth\lib\jwt\lib\jwtException;

class Jwt
{

    protected hmac $hmac;
    protected base64 $base64;

    public function __construct(
        protected config $config
    ) {
        $this->hmac = new hmac();
        $this->base64 = new base64();
    }

    /**
     * 获取JWT Token
     * @return string
     */
    public function getToken(): string
    {
        $header = $this->base64 -> base64UrlEncode(json_encode(
            $this->config -> getHeader(), JSON_UNESCAPED_UNICODE
        ));
        $payload = $this->base64 -> base64UrlEncode(json_encode(
            $this->config -> getPayload(), JSON_UNESCAPED_UNICODE
        ));

        return $header . '.' . $payload . '.' . $this->getSignature($header . '.' . $payload);
    }

    /**
     * 验证Jwt Token
     * @param string $token
     * @return mixed
     * @throws jwtException
     */
    public function verifyToken(string $token)
    {
        $tokens = explode('.', $token);
        if (count($tokens) !== 3) {
            throw new jwtException('JWT 格式错误');
        }
        [$header, $payload, $sign] = $tokens;

        $headerArray = json_decode($this->base64 -> base64UrlDecode($header), JSON_OBJECT_AS_ARRAY);

        if (empty($headerArray['alg'])) throw new jwtException('jwt 加密格式错误');

        if ($this->hmac -> verify(
            $headerArray['alg'], $sign, $header.$payload, $this->config -> getKey()
        )) throw new jwtException('jwt 签名验证失败');

        $payload = json_decode($this->base64 -> base64UrlDecode($payload), JSON_OBJECT_AS_ARRAY);

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time())
            throw new jwtException('jwt 签发时间错误');

        if (isset($payload['exp']) && $payload['exp'] < time())
            throw new jwtException('jwt 已超时');

        if (isset($payload['nbf']) && $payload['nbf'] > time())
            throw new jwtException('jwt 暂未生效');

        return $payload;
    }

    protected function getPayload(): string
    {
        return $this -> base64 -> base64UrlEncode(
            json_encode($this->config -> getPayload(), JSON_UNESCAPED_UNICODE)
        );
    }

    protected function getHeader(): string
    {
        return $this -> base64 -> base64UrlEncode(
            json_encode($this->config -> getHeader(), JSON_UNESCAPED_UNICODE)
        );
    }

    protected function getSignature($data): string
    {
        return $this -> base64 -> base64UrlEncode(
            $this->hmac -> sign($this->config -> getHeader()['alg'], $data, $this->config -> getKey())
        );
    }
}