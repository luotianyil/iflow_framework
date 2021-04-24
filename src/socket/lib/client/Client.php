<?php


namespace iflow\socket\lib\client;


class Client
{

    private mixed $handle = null;

    public bool $connected = false;

    public array $setting = [
        'open_eof_check' => true,
        'package_eof' => "\r\n",
        'package_max_length' => 65536
    ];

    public string $errMsg = "";
    public int $errCode = 0;

    public function __construct(protected bool $enableSSL = true)
    {}

    public function connect(string $host, int $port, float $timeout): bool
    {
        $this->handle = stream_socket_client("$host:$port", $this->errCode, $this->errMsg, $timeout);

        if ($this->handle) {
            if ($this->enableSSL) {
                $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                }
                stream_socket_enable_crypto($this->handle, true, $crypto_method);
            }
            stream_set_blocking($this->handle, false);
            $this->connected = true;
        }
        return $this->connected;
    }

    public function set(array $options = [])
    {
        $this->setting = array_merge($options, $this->setting);
    }

    public function send(string $data): bool|int
    {
        return fwrite($this->handle, $data);
    }

    public function recv(float $timeout): string
    {
        stream_set_timeout($this->handle, $timeout);
        while (true) {
            $pack = @fgets($this->handle, $this->setting['package_max_length']);
            if ($pack) break;
        }
        return $pack;
    }

    public function close(): bool
    {
        return fclose($this->handle);
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }
}