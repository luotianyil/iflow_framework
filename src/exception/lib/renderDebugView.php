<?php


namespace iflow\exception\lib;

use iflow\Response;
use iflow\template\Template;
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


    public function render(): Response
    {
        if (!file_exists($this->exception_tpl)) {
            $this->exception_tpl = str_replace("\\", '/', __DIR__ . "/../exception.tpl");
        }


        if (!file_exists($this->exception_tpl)) {
            return message() -> server_error(
                502, $this->exception_tpl.' template file not does exists', $this->getError());
        }

        return (new Template())
            -> setData($this->getError()) -> send($this->exception_tpl);
    }

    public function getError(): array
    {
        return [
            'code' => $this->throwable -> getCode(),
            'msg' => $this->throwable -> getMessage(),
            'file' => $this->throwable -> getFile(),
            'line' => $this->throwable -> getLine(),
            'source' => $this->getThrowExceptionFileContent(),
            'trace' => $this->trace
        ];
    }


    public function getThrowExceptionFileContent(): array
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


}