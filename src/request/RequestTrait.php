<?php


namespace iflow\request;

use GuzzleHttp\Psr7\{BufferStream, ServerRequest};
use iflow\request\lib\{helper, validRequest};
use Psr\Http\Message\{RequestInterface, ServerRequestInterface, StreamInterface};

trait RequestTrait
{
    use validRequest, helper;

    protected string $version = "";
    protected ?RequestInterface $requestPsr7 = null;
    protected ?ServerRequestInterface $serverRequestPsr7 = null;

    /**
     * 当前请求资源router
     * @var array
     */
    protected array $router = [];

    /**
     * 获取HTTP版本
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * 获取标准 PSR7 RequestInterface
     * @return RequestInterface
     */
    public function getRequestPsr7(): RequestInterface
    {
        if ($this->requestPsr7 !== null) $this->requestPsr7;
        $this->requestPsr7 = new \GuzzleHttp\Psr7\Request(
            $this->request_method,
            $this->getRequestUri(),
            $this->getHeader(),
            $this->getBufferStream(),
            $this->version
        );
        return $this->requestPsr7;
    }

    /**
     * 获取标准 PSR7 ServerRequestInterface 类
     * @return ServerRequestInterface
     */
    public function getServerRequestPsr7(): ServerRequestInterface
    {
        if ($this->serverRequestPsr7 !== null) return $this->serverRequestPsr7;
        $this->serverRequestPsr7 = (
            new ServerRequest(
                $this->request_method,
                $this->getRequestUri(),
                $this->getHeader(),
                $this->getBufferStream(),
                $this->version,
                $this->server
            )
        ) -> fromGlobals();
        return $this->serverRequestPsr7;
    }

    /**
     * 获取BufferStream
     * @return StreamInterface
     */
    public function getBufferStream(): StreamInterface {
        $row = $this->postParams();
        $row = !is_string($row) ? json_encode($row, JSON_UNESCAPED_UNICODE) : $row;

        $buffer = new BufferStream();
        $buffer->write($row);
        return $buffer;
    }

    /**
     * 初始化请求参数
     * @return $this
     */
    public function initRequestParams(): static {
        $_GET = $this->getParams();
        $_POST = $this->postParams();
        $_COOKIE = $this->request -> cookie -> get();
        $_SERVER = $this->server;
        $_FILES = $this->request->files;
        return $this;
    }

    /**
     * @param array $router
     * @return RequestTrait
     */
    public function setRouter(array $router): static {
        $this->router = $router;
        return $this;
    }

    /**
     * @return array
     */
    public function getRouter(): array {
        return $this->router;
    }
}