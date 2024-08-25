<?php


namespace iflow\exception\Adapter;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\http\ResponseStatus;
use iflow\Response;
use iflow\template\View;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RenderDebugView {

    protected string $exception_tpl = "";
    protected string $trace = "";

    public function __construct(protected Throwable $throwable, protected array $config) {
        $this->exception_tpl =
            $this->config['exception_tpl'] ?? str_replace("\\", '/', __DIR__ . "/../exception.tpl");
        $this->trace = $this->throwable -> getTraceAsString();
    }


    /**
     * 渲染数据
     * @param Response|ResponseInterface|Throwable|null $response
     * @return Response
     * @throws InvokeClassException
     */
    public function render(Response|ResponseInterface|Throwable|null $response = null): Response {

        $throwable =  $response ? (
            !$response instanceof Throwable
                ? new HttpResponseException($response)
                : $response
        ): $this->throwable;

        // 此处验证是否为Response异常
        if ($throwable instanceof HttpResponseException) {
            $response = $throwable -> getResponse();
            return match ($response) {
                $response instanceof ResponseInterface => response()
                    ->headers($response->getHeaders())
                    ->withStatus($response->getStatusCode())
                    ->data($response->getBody()->__toString()),
                default => $response,
            };
        }

        return $this->httpServerThrowException();
    }

    /**
     * 获取异常数据
     * @param array $keys
     * @return array
     */
    public function getError(array $keys = []): array {
        $errors =  [
            'code' => $this->throwable -> getCode(),
            'msg' => $this->throwable -> getMessage(),
            'file' => $this->throwable -> getFile(),
            'line' => $this->throwable -> getLine(),
            'source' => $this->getThrowExceptionFileContent(),
            'trace' => $this->trace,
            'traceArr' => $this->throwable -> getTrace()
        ];

        if (empty($keys)) return $errors;

        return array_map(fn ($key) => $key . '：' . ($errors[$key] ?? ''), $keys);
    }

    /**
     * 获取异常文件内容
     * @return array
     */
    protected function getThrowExceptionFileContent(): array {
        // 获取抛出异常文件内容 附近几行
        $file = $this->throwable -> getFile();
        $line = $this->throwable -> getLine() - 3;
        $startLine = 1;

        $source = [];

        if (!file_exists($file)) return [];

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
     * @throws InvokeClassException
     */
    protected function httpServerThrowException(): Response|array {

        $error = app() -> isDebug() ? $this->getError() : [];

        // 非HTTP服务
        if (!is_http_services()) {
            dump($this->getError([ 'code', 'msg', 'file', 'line', 'trace' ]));
            return [];
        }

        $response = message() -> setIsRest();

        // 如果为Ajax请求
        if (request() -> isAjax()) {
            return $response -> server_error($this->throwable -> getCode(), $this->throwable->getMessage(), $error);
        }

        if (!file_exists($this->exception_tpl)) {
            $this->exception_tpl = str_replace("\\", '/', __DIR__ . "/../exception.tpl");
        }

        if (!file_exists($this->exception_tpl)) {
            return $response -> server_error(502, $this->exception_tpl.' template file does not exists', $error);
        }

        return (new View()) -> render($this->exception_tpl, $error) -> withStatus(
            $this->getResponseStatus($this->throwable -> getCode())
        );
    }


    /**
     * 获取Response 响应CODE
     * @param int $code
     * @return int
     */
    public function getResponseStatus(int $code): int {
        $responseCodes = array_keys(ResponseStatus::RESPONSE_STATUS);
        return in_array($code, $responseCodes) ? $code : 502;
    }
}