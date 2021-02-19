<?php


namespace iflow\Swoole\email\lib\Message;


class Html extends messageBase
{
    public function setHtml(string $html = ''): static
    {
        $this->body .= $html . "\r\n";
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        $header = "Content-Type: text/html; charset={$this -> getCharSet()} \r\n";
        $header .= parent::getHeader();
        return $header;
    }
}