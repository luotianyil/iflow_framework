<?php


namespace iflow\auth\Authorize\Jwt\Tools;


class hmac
{
    protected array $enum = [
        'HS256' => 'SHA256',
        'HS384' => 'SHA384',
        'HS512' => 'SHA512'
    ];

    public function sign(string $alg, string $data, $key): string {
        return hash_hmac($this->enum[$alg], $data, $key);
    }

    public function verify(string $alg, string $sign, string $data, string $key): bool {
        return hash_equals($sign, $this->sign($alg, $data, $key));
    }
}