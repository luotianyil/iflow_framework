<?php


namespace iflow\auth\Authorize\Jwt\Tools;


class base64
{

    public function base64UrlEncode(string $code): string {
        return rtrim(strtr(base64_encode($code), '+/', '-_'), '=');
    }


    public function base64UrlDecode(string $code): string {
        if ($remainder = strlen($code) % 4) {
            $code .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($code, '-_', '+/'));
    }

}