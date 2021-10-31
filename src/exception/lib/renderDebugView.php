<?php


namespace iflow\exception\lib;

use iflow\console\Console;
use iflow\Response;
use iflow\template\View;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class renderDebugView
{

    protected string $exception_tpl = "";
    protected string $trace = "";

    public function __construct(protected Throwable $throwable, protected array $config) {

        $this->exception_tpl =
            $this->config['exception_tpl'] ?? str_replace("\\", '/', __DIR__ . "/../exception.tpl");

        $this->trace = $this->throwable -> getTraceAsString();
    }


    /**
     * 渲染数据
     * @return ?Response
     */
    public function render(): ?Response
    {
        // 此处验证是否为Response异常
        if ($this->throwable instanceof HttpResponseException) {
            $response = $this->throwable -> getResponse();
            switch ($response) {
                case $response instanceof ResponseInterface:
                    // PSR7
                    return response()
                        -> headers($response -> getHeaders())
                        -> withStatus($response -> getStatusCode())
                        -> data($response -> getBody() -> __toString());
                default :
                    return $response;
            }
        }

        if (is_http_services()) {
            return $this->httpServerThrowException();
        }

        dump($this->getError());
        return null;
    }

    /**
     * 获取异常数据
     * @return array
     */
    public function getError(): array
    {
        return [
            'code' => $this->throwable -> getCode(),
            'msg' => $this->throwable -> getMessage(),
            'file' => $this->throwable -> getFile(),
            'line' => $this->throwable -> getLine(),
            'source' => $this->getThrowExceptionFileContent(),
            'trace' => $this->trace,
            'traceArr' => $this->throwable -> getTrace()
        ];
    }

    /**
     * 获取异常文件内容
     * @return array
     */
    protected function getThrowExceptionFileContent(): array
    {
        // 获取抛出异常文件内容 附近几行
        $file = $this->throwable -> getFile();
        $line = $this->throwable -> getLine() - 3;
        $startLine = 1;

        $source = [];
        $fp = fopen($file, 'r');

        while (!feof($fp)) {
            if ($startLine > $line + 10) break;
            $buffer = fgets($fp);
            if ($startLine >= $line) {
                $source[$startLine] = $buffer;
            }
            $startLine++;
        }
        return $source;
    }

    /**
     * 验证是否为HTTP服务
     * @return array|Response
     */
    protected function httpServerThrowException(): Response|array
    {
        // 如果为Ajax请求
        if (request() -> isAjax()) {
            return message() -> server_error(
                $this->throwable -> getCode(), $this->throwable->getMessage(), $this->getError()
            );
        }

        if (!file_exists($this->exception_tpl)) {
            $this->exception_tpl = str_replace("\\", '/', __DIR__ . "/../exception.tpl");
        }

        if (!file_exists($this->exception_tpl)) {
            return message() -> server_error(
                502, $this->exception_tpl.' template file does not exists', $this->getError());
        }

        return (new View()) -> render(
            $this->exception_tpl, $this->getError()
        );
    }
}