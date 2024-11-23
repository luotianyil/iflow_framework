<?php


namespace iflow\response;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\exception\Adapter\HttpResponseException;
use iflow\Response;
use Psr\Http\Message\ResponseInterface;

trait ResponseTrait {

    public array $headers = [];

    public string $contentType = 'text/html';

    public string $charSet = 'utf-8';

    public int $code = 200;

    public mixed $data = '';

    public array $options = [];

    public mixed $response = null;

    // HTTP 版本
    public string $version = "1.1";

    // 运行开始时间
    public float $startTime = 0.00;

    public ?ResponseInterface $responsePsr7 = null;

    /**
     * 设置响应对象
     * @param $response
     * @return Response
     */
    protected function response($response): Response {
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
     * @return Response
     */
    public function data($data): Response {
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
     * @return Response
     */
    public function withStatus(int $status): Response {
        $this->code = $status;
        return $this;
    }

    /**
     * 其他设置
     * @param array $options
     * @return Response
     */
    public function options(array $options = []): Response
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 设置响应头
     * @param array $headers
     * @return Response
     */
    public function headers(array $headers = []): Response
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
     * @return Response
     */
    public function setLastModified(string $value = ""): Response
    {
        $this->headers([
            'Last-Modified' => $value ?: gmdate('D,d M Y H:i:s')."GMT"
        ]);
        return $this;
    }

    /**
     * 设置响应缓存
     * @param string $value
     * @return Response
     */
    public function setCacheControl(string $value = ""): Response
    {
        $this->headers([
            'Cache-Control' => $value ?: "max-age=36000,must-revalidata"
        ]);
        return $this;
    }

    /**
     * 设置静态文件过期时间
     * @param string $value
     * @return Response
     */
    public function steExpiresTimes(string $value = ""): Response
    {
        $this->headers([
            'Expires' => $value ?: gmdate('D,d M Y H:i:s',time() + 36000)."GMT"
        ]);
        return $this;
    }

    /**
     * 设置内容Type
     * @param string $contentType
     * @return Response
     */
    public function contentType(string $contentType = 'application/json') : Response {
        $this -> contentType = $contentType;
        return $this;
    }

    /**
     * 设置重定向地址
     * @param string $url
     * @return Response
     */
    public function setRedirect(string $url = ""): Response
    {
        $this->code = 302;
        $this->headers["Location"] = $url;
        return $this;
    }

    /**
     * 设置HTTP 响应版本
     * @param string $version
     * @return Response
     */
    public function setVersion(string $version): Response
    {
        $this->version = $version;
        return $this;
    }

    /**
     * 设置PSR7
     * @param ResponseInterface|null $responsePsr7
     * @return Response
     */
    public function responsePsr7(?ResponseInterface $responsePsr7 = null): Response
    {
        $this->responsePsr7 = $responsePsr7;
        return $this;
    }
}