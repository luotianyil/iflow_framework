<?php


namespace iflow\Swoole\email;


use iflow\Swoole\email\lib\client;
use iflow\Swoole\email\lib\Config;
use iflow\Swoole\email\lib\Message\Html;
use iflow\Swoole\email\lib\Message\Text;

class Mailer
{

    protected array $to = [];

    protected string $body = '';
    protected array $header = [];
    protected Config $config;

    private client $client;

    public function __construct(string $config = '')
    {
        $this->setConfig($config);
        $this -> client = new client($this, $this->config);
    }


    protected function setConfig($name): static
    {
        $config = config('email');
        $name = $name === "" ? $config['default'] : $name;
        $this->config = new Config($config['email'][$name]);
        return $this;
    }

    /**
     * @param array|string $to
     * @return Mailer
     */
    public function setTo(array|string $to): static
    {

        if (is_string($to)) $this->to[] = $to;
        else {
            $this->to = array_replace_recursive($this->to, $to)??[];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    public function send(Html|Text $message): bool
    {
        return $this -> client -> push($message);
    }

}