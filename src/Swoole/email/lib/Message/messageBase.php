<?php


namespace iflow\Swoole\email\lib\Message;


abstract class messageBase
{
    use attachment;

    protected array $header = [];
    protected mixed $body = "";
    protected string $Subject = "";
    protected array $Cc = [];

    CONST EOF = "\r\n";

    public function setHeader(array $header): static
    {
        $this->header = array_replace_recursive($this->header, $header) ?? [];
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        $header = "";
        foreach ($this->header as $key => $value) {
            $header .= "{$key}: {$value}". self::EOF;
        }
        $header .= "Content-Transfer-Encoding: quoted-printable". self::EOF;
        $header .= 'Date: '. date('r') . self::EOF;
        return $header;
    }

    public function setSubject(string $Subject = ""): static
    {
        $this->Subject = "Subject: ". $Subject . self::EOF;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->Subject;
    }

    public function setCc(string $cc): static
    {
        $this->Cc[] = $cc;
        return $this;
    }

    /**
     * @param string $charSet
     * @return messageBase
     */
    public function setCharSet(string $charSet): static
    {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharSet(): string
    {
        return $this->charSet;
    }

    public function getCc(): array
    {
        return $this->Cc;
    }

    public function getBody(): string
    {
        return
            empty($this->attachment) ? self::EOF . $this->body :
            $this->attachmentToBody($this->body);
    }
}