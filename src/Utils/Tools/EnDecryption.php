<?php


namespace iflow\Utils\Tools;


class EnDecryption {

    public array $config = [];

    public function __construct() {
        $this->config = config('enDecryption@openssl');
    }

    /**
     * 对称加密
     * @param string $data
     * @param int $padding
     * @return string
     */
    public function SSLEncryption(string $data = '', int $padding = OPENSSL_PKCS1_PADDING) : string {
        $encrypt_success = openssl_public_encrypt($data,$encryption, $this->config['ssl']['publicKey'], $padding);
        return $encrypt_success ? base64_encode($encryption) : false;
    }

    /**
     * 对称解密
     * @param string $data
     * @param int $padding
     * @return string
     */
    public function SSLDecryption(string $data = '', int $padding = OPENSSL_PKCS1_PADDING) : string
    {
        $decryption_success = openssl_private_decrypt(
            base64_decode($data), $decryption, $this->config['ssl']['privateKey'], $padding
        );
        return $decryption_success ? $decryption : false;
    }

    /**
     * openssl 加密
     * @param string $data
     * @param string $method
     * @param string $key
     * @param int $option
     * @param string $iv
     * @param mixed $tag
     * @param ...$args
     * @return string
     */
    public function encryption(string $data = '', string $method = 'DES-ECB', string $key = '', int $option = 0, string $iv = '', mixed $tag = '',  ...$args) : string
    {
        $key = $key !== '' ? $key : $this->config['encryption_key'];
        $iv = $iv !== '' ? $iv : $this->config['iv'];
        return base64_encode(openssl_encrypt($data, $method, $key, $option, $iv, $tag, ...$args));
    }

    /**
     * openssl 解密
     * @param string $data
     * @param string $method
     * @param string $key
     * @param int $option
     * @param string $iv
     * @param ...$args
     * @return string
     */
    public function decryption(string $data = '', string $method = 'DES-ECB', string $key = '', int $option = 0, string $iv = '', ...$args) : string {
        $key = $key !== '' ? $key : $this->config['encryption_key'];
        $iv = $iv !== '' ? $iv : $this->config['iv'];
        return openssl_decrypt($data, $method, $key, $option, $iv, ...$args);
    }

    /**
     * 密码加密
     * @param string $password
     * @param string $algo
     * @return string
     */
    public static function passwordHash(string $password = '', string $algo = PASSWORD_DEFAULT): string {
        return password_hash($password, $algo);
    }

    /**
     * 密码验证
     * @param string $hashPassword
     * @param string $password
     * @return bool
     */
    public static function passwordVerify(string $hashPassword, string $password): bool {
        return password_verify($password, $hashPassword);
    }

}