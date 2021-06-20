<?php


namespace iflow\exception\lib;


class HttpException extends \RuntimeException
{
    private int $statusCode;
    private array $headers;

    public function __construct(int $statusCode, string $message = '', \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
