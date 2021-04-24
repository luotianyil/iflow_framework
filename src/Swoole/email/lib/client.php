<?php


namespace iflow\Swoole\email\lib;

use iflow\socket\lib\client\Client as sClient;
use iflow\Swoole\email\lib\Exception\mailerException;
use iflow\Swoole\email\lib\Message\Html;
use iflow\Swoole\email\lib\Message\Text;
use iflow\Swoole\email\Mailer;

class client
{
    protected mixed $client;

    public function __construct(
        protected Mailer $mailer,
        protected Config $config
    ){}

    protected function connection(): static
    {

        $this->client = class_exists(\Swoole\Coroutine\Client::class) ? new \Swoole\Coroutine\Client(
            $this->config -> getSsl() ? SWOOLE_SSL | SWOOLE_TCP : SWOOLE_TCP
        ) : new sClient($this->config -> getSsl());
        $this->client -> set($this->config -> getOptions());

        if (!$this->client -> connect(
            $this->config -> getHost(),
            $this->config -> getPort(),
            $this->config -> getTimeOut()
        )) {
            throw new mailerException(
                'smtpServer connection error: '. $this->client -> errMsg . 'code: '. $this->client -> errCode
            );
        }
        return $this;
    }

    protected function ehlo($host)
    {
        $this->send("ehlo {$host}", 250);
        while (1) {
            $receive = $this->getRecv();
            if (substr($receive, 3, 1) !== '-') {
                break;
            }
        }
        return $this;
    }

    protected function emailLogin()
    {
        $this->send('AUTH LOGIN', 334);
        $this->send(base64_encode($this->config -> getUserName()), 334);
        $this->send(base64_encode($this->config -> getPassWord()), 235);
        return $this;
    }

    protected function toMail($message) {
        $this->send("mail from:<{$this -> config -> getForm()}>", 250);
        foreach ($this->mailer -> getTo() as $key) {
            $this->send("rcpt to:<{$key}>", 250);
        }
        $this->send('data', 354);
        $this->send($this->setMailBody($message));
        $this->send(".", 250);
        return $this->close();
    }

    protected function setMailBody(Html|Text $message): string
    {
        $header = $message -> getHeader();
        $header .= "MIME-Version: ". $this->config -> mimeVersion(). "\r\n";
        $header .= "X-Mailer: By (PHP/" . phpversion() . ")\r\n";
        $header .= "From: {$this -> config -> getFormName()}<{$this -> config -> getForm()}>\r\n";
        $header .= $message -> getSubject();
        foreach ($this->mailer -> getTo() as $key) {
            $header .= "To: <{$key}>\r\n";
        }

        foreach ($message -> getCc() as $value) {
            $header .= "Cc: <" . $value . "> \r\n";
        }
        return $header . $message -> getBody();
    }

    public function send(?string $command, int $code = -1)
    {
        $send = $this->client -> send(trim($command, "\r\n") . "\r\n");
        return $code > 0 ? $this->recvCode($code) : $send;
    }

    public function push($message)
    {
        $this->connection();
        $recv = $this->recvCode(220);
        return $this -> ehlo($recv[1])
                     -> emailLogin()
                     -> toMail($message);
    }

    protected function getRecv() {
        return $this->client -> recv($this->config -> getTimeOut());
    }

    public function recvCode(int $code)
    {
        $recv = $this->getRecv();
        if ($recv) {
            $recvCode = str_contains($recv, $code);
            $recv = explode(' ', $recv);
            return $recvCode ? $recv : throw new mailerException("smtpServer code fail need {$code}");
        }
        return null;
    }

    protected function close()
    {
        if ($this->client -> isConnected()) {
            $this->client -> send("QUIT");
            $this->client -> close();
        }
        return true;
    }
}