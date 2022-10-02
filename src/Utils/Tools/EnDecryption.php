<?php


namespace iflow\Utils\Tools;


class EnDecryption
{

    public array $config = [];

    public function __construct()
    {
        $this->config = config('enDecryption@openssl');
    }

    // 对称加密
    public function SSLEncryption(string $data = "") : string
    {
        $encrypt_success = openssl_public_encrypt($data,$encryption, $this->config['ssl']['publicKey']);
        return $encrypt_success ? base64_encode($encryption) : false;
    }

    // 对称解密
    public function SSLDecryption(string $data = "") : string
    {
        $decryption_success = openssl_private_decrypt(base64_decode($data), $decryption, $this->config['ssl']['privateKey']);
        return $decryption_success ? $decryption : false;
    }

    // openssl 加密
    public function Encryption($data = '', $method = 'DES-ECB', $key = '', $option = 0, $iv = '') : string
    {
        $key = $key !== '' ? $key : $this->config['encryption_key'];
        $iv = $iv !== '' ? $iv : $this->config['iv'];
        return base64_encode(openssl_encrypt($data, $method, $key, $option, $iv));
    }

    // openssl 解密
    public function Decryption($data = '', $method = 'DES-ECB', $key = '', $option = 0, $iv = '') : string {
        $key = $key !== '' ? $key : $this->config['encryption_key'];
        $iv = $iv !== '' ? $iv : $this->config['iv'];
        return openssl_decrypt($data, $method, $key, $option, $iv);
    }

    // 密码加密
    public function passwordHash($passWord = '', $algo = null): string {
        return password_hash($passWord, $algo === null ? PASSWORD_DEFAULT : $algo);
    }

    // 密码验证
    public function passwordVerify(string $hashPassword, string $password): bool {
        return password_verify($password, $hashPassword);
    }

}