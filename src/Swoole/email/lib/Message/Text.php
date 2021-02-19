<?php


namespace iflow\Swoole\email\lib\Message;


class Text extends messageBase
{

    public function setText(string $content = "")
    {
        $this->body .= $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        $header = "Content-Type: text/plain; charset={$this -> getCharSet()} \r\n";
        $header .= parent::getHeader();
        return $header;
    }
}