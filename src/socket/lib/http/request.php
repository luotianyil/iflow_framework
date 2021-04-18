<?php


namespace iflow\socket\lib\http;


use iflow\http\lib\Cookie;

class request
{

    public array $server = [];

    public array $get = [];
    public array $post = [];
    public array $header = [];
    public array $files = [];

    public array $request;
    public string $input;
    public string $request_method = 'GET';
    public string $request_uri = "/";
    public string $request_protocol = "";
    public array $rowContent = [];
    public cookie $cookie;

    public function __construct(
        // 客户端请求主体
        protected string $body,
        protected $socket,
        protected array $options = []
    ) {
        $this->options['tempDir'] = $this->options['tempDir'] ?: sys_get_temp_dir();
       if ($this->initRequest() === false) {
           throw new \Exception("server_protocol is null");
       }
    }


    protected function initRequest(): static|bool
    {
        [$header, $this->input] = explode("\r\n\r\n", $this->body);
        $header = explode("\r\n", $header);
        if (count(explode(" ", $header[0])) < 3) {
            return false;
        }

        [
            $this->request_method,
            $this->request_uri,
            $this->request_protocol
        ] = explode(" ", array_shift($header));
        return $this -> setHeaders($header) -> setServer() -> setRowContent() -> setParams();
    }

    /**
     * 获取原始请求包体
     * @return array
     */
    public function getContent(): array
    {
        return $this->rowContent;
    }

    /**
     * 设置请求主体信息
     * @return $this
     */
    protected function setServer(): static
    {

        $request_uri = explode("?", $this->request_uri);

        $this->server = $this->header;
        $this->server['request_method'] = $this->request_method;
        $this->server['request_uri'] = $this->request_uri;
        $this->server['path_info'] = $request_uri[0];
        $this->server['server_protocol'] = $this->request_protocol;
        $this->server['request_time'] = time();
        $this->server['request_time_float'] = $this->server['request_time'];

        //服务端监听端口
        $this->server['server_port'] = explode(':', $this->header['host'])[1];

        // 客户端地址
        socket_getpeername($this->socket,
            $this->server['remote_addr'],
            $this->server['remote_port']
        );

        $this->server['query_string'] = $request_uri[1] ?? "";
        return $this;
    }

    /**
     * 设置请求头
     * @param array $headers
     * @return $this
     */
    protected function setHeaders(array $headers = []): static
    {
        foreach ($headers as $header) {
            $key = substr($header, 0, strpos($header, ":"));
            $value = substr($header, strpos($header, ":") + 1);
            $this->header[strtolower(str_replace("-", "_", $key))] = trim($value);
        }
        return $this;
    }

    /**
     * 初始化请求包体
     * @return $this
     */
    protected function setRowContent(): static
    {
        if ($this->input !== "") {
            $contentType = $this->header['content_type'] ?? $this->header['accept'];
            if ('application/x-www-form-urlencoded' == explode(';', $contentType)[0]) {
                parse_str($this->input, $this->rowContent);
            } else if ('multipart/form-data' === explode(';', $contentType)[0]) {
                $this->parseMultipartFormData($contentType);
            } else {
                $this->rowContent = (array) json_decode($this->input, true);
            }
        }
        return $this;
    }

    /**
     * 初始化请求参数
     * @return $this
     */
    protected function setParams(): static
    {
        // 设置get参数
        if ($this->server['query_string'] !== "") {
            parse_str($this->server['query_string'], $this->get);
        }
        // 设置post参数
        $this->post = $this->rowContent;

        // 设置cookie
        $this->cookie = new Cookie($this->parseCookie($this->server['cookie'] ?? ''));
        return $this;
    }

    /**
     * 解析cookie
     * @param string $cookie
     * @return array
     */
    protected function parseCookie($cookie = ''): array
    {
        $cookies = [];
        if ($cookie !== "") {
            $cookie = explode(';', $cookie);
            foreach ($cookie as $value) {
                [$key, $value] = explode('=', $value);
                $cookies[$key] = $value;
            }
        }
        return $cookies;
    }

    /**
     * 解析 MultipartFormData 请求包体
     * @param string $contentType
     */
    protected function parseMultipartFormData(string $contentType)
    {
        $contentType = explode(';', $contentType);
        $data = explode(explode("=", $contentType[1])[1], $this->body);
        // 取出 头部信息 只保留包体
        array_shift($data);
        array_shift($data);

        // 格式化包体数据
        foreach ($data as $key => $value) {
            // 分割 内容与头部信息
            [$name, $val] = explode("\r\n\r\n", $value);

            $header = explode("\r\n", $name);

            // 格式化 key
            preg_match("/^.*?name=\"(.*?)\"(|(; filename=\"(.*?)\"))$/", $header[1], $reg);

            if ($reg) {
                if (count($reg) === 5) {
                    // 储存临时文件
                    $tempPath = $this->options['tempDir'] . DIRECTORY_SEPARATOR . $reg[4];
                    $this->saveTempFile($tempPath, $val);

                    $this->files[$reg[1]][] = [
                        'tmp_name' => $tempPath,
                        'content-type' => $header[2]
                    ];
                } else {
                    $this->rowContent[$reg[1]] = explode("\r\n", $val)[0];
                }
            }
        }
    }

    /**
     * 存储请求临时文件
     * @param $path
     * @param $content
     */
    protected function saveTempFile($path, $content)
    {
        $dir = dirname($path);
        is_dir($dir) && mkdir($dir);
        file_put_contents($path, $content);
    }
}