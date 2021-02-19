<?php


namespace iflow\Swoole\email\lib;

class Config
{

    public function __construct(
        protected array $config = []
    )
    {}

    public function getHost(): string
    {
        return $this->config['smtpHost'] ?? '';
    }

    public function getPort(): int
    {
        return $this->config['smtpPort'] ?? 0;
    }

    public function getSsl(): bool
    {
        return $this->config['ssl'] ?? true;
    }

    public function getUserName(): string
    {
        return $this->config['userName'] ?? '';
    }

    public function getPassWord(): string
    {
        return $this->config['passWord'] ?? '';
    }

    public function getTimeOut(): int|float
    {
        return $this->config['timeOut'] ?? 30;
    }

    public function getOptions(): array
    {
        return $this->config['options'] ??
            [
                'open_eof_check' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 1024 * 1024 * 2
            ];
    }

    public function getForm()
    {
        return $this->config['from'] ?? '';
    }

    public function getFormName()
    {
        return $this->config['fromName'] ?? '';
    }

    public function getCharSet()
    {
        return $this->config['charSet'] ?? 'utf-8';
    }

    public function mimeVersion()
    {
        return $this->config['mimeVersion'] ?? '1.0';
    }
}