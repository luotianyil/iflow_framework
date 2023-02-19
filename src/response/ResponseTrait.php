<?php


namespace iflow\response;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\exception\Adapter\HttpResponseException;
use Psr\Http\Message\ResponseInterface;

trait ResponseTrait
{

    public array $headers = [];
    public string $contentType = 'text/html';
    public string $charSet = 'utf-8';
    public int $code = 200;
    public mixed $data = '';
    public array $options = [];
    public mixed $response = null;

    // HTTP 版本
    public string $version = "1.1";
    public ?ResponseInterface $responsePsr7 = null;

    /**
     * 设置响应对象
     * @param $response
     * @return $this
     */
    protected function response($response): static {
        $this->response = $response;
        return $this;
    }

    /**
     * 设置 404 返回
     * @param string $msg
     * @return bool
     * @throws InvokeClassException
     */
    public function notFount(string $msg = '404 Not-Found'): bool
    {
        $this -> code = 404;
        if (request() -> isAjax() === false) {
            $path = config('app@404_error_page');
            if (file_exists($path)) {
                throw new HttpResponseException($this->sendFile($path, false));
            }
        }
        throw new HttpResponseException(message() -> nodata($msg));
    }

    /**
     * 设置返回响应数据
     * @param $data
     * @return $this
     */
    public function data($data): static {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置返回内容格式
     * @param string $charSet
     */
    public function charSet(string $charSet = 'utf-8'): void {
        $this->charSet = $charSet;
    }

    /**
     * 设置响应状态
     * @param int $status
     * @return $this
     */
    public function withStatus(int $status): static {
        $this->code = $status;
        return $this;
    }

    /**
     * 其他设置
     * @param array $options
     * @return $this
     */
    public function options(array $options = []): static
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 设置响应头
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers = []): static
    {
        // 解决 HeaderValues 为 Array
        $headerMap = [];
        foreach ($headers as $headerKey => $header) {
            $headerMap[$headerKey] = is_array($header) ? array_shift($header) : $header;
        }
        $this->headers = array_merge($this->headers, $headerMap);
        return $this;
    }

    /**
     * 设置 Modified 标识
     * @param string $value
     * @return $this
     */
    public function setLastModified(string $value = ""): static
    {
        $this->headers([
            'Last-Modified' => $value ?: gmdate('D,d M Y H:i:s')."GMT"
        ]);
        return $this;
    }

    /**
     * 设置响应缓存
     * @param string $value
     * @return $this
     */
    public function setCacheControl(string $value = ""): static
    {
        $this->headers([
            'Cache-Control' => $value ?: "max-age=36000,must-revalidata"
        ]);
        return $this;
    }

    /**
     * 设置静态文件过期时间
     * @param string $value
     * @return $this
     */
    public function steExpiresTimes(string $value = ""): static
    {
        $this->headers([
            'Expires' => $value ?: gmdate('D,d M Y H:i:s',time() + 36000)."GMT"
        ]);
        return $this;
    }

    /**
     * 设置内容Type
     * @param string $contentType
     * @return $this
     */
    public function contentType(string $contentType = 'application/json') : static {
        $this -> contentType = $contentType;
        return $this;
    }

    /**
     * 设置重定向地址
     * @param string $url
     * @return $this
     */
    public function setRedirect(string $url = ""): static
    {
        $this->code = 302;
        $this->headers["Location"] = $url;
        return $this;
    }

    /**
     * 设置HTTP 响应版本
     * @param string $version
     * @return static
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    /**
     * 设置PSR7
     * @param ResponseInterface|null $responsePsr7
     * @return $this
     */
    public function responsePsr7(?ResponseInterface $responsePsr7 = null): static
    {
        $this->responsePsr7 = $responsePsr7;
        return $this;
    }
}