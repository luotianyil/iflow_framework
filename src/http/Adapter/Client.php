<?php


namespace iflow\http\Adapter;

class Client {

    protected object $curl;

    public string $method = 'GET';
    public array $headers = [];
    public array $data = [];

    public string $responseHeaders = '';
    public string $responseRedirectHeaders = '';
    public string $body = '';

    public function __construct(public string $host = '', public int $port = 0, public bool $isSSL = false) {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
    }

    public function set($option = []) {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $option['timeout']??30);
    }

    /**
     * @param string $method
     * @return static
     */
    public function setMethod(string $method): static {
        $this->method = strtoupper($method);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
        return $this;
    }

    /**
     * @param array $headers
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        $h = [];
        array_walk($headers, function ($v, $k) use (&$h) {
            $h[] = "$k:$v";
        });
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $h);
        return $this;
    }

    /**
     * @param array|string $data
     * @return static
     */
    public function setData(array|string $data): static {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if ($this->method === 'POST')  curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    public function setCookies(string $cookies): static {
        curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);
        return $this;
    }

    /**
     * 设置请求证书
     * @param string $keyFile
     * @param string $certFile
     * @return $this
     */
    public function setSSL(string $keyFile = "", string $certFile = ""): static {
        if (file_exists($keyFile) && file_exists($certFile)) {
            curl_setopt($this->curl, CURLOPT_SSLKEY, $keyFile);
            curl_setopt($this->curl, CURLOPT_SSLCERT, $certFile);
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        }
        return $this;
    }

    public function execute($path = ''): bool {
        $scheme = $this->isSSL ? 'https://' : 'http://';
        $host = $this->host;

        if ($this->port !== 0) {
            $host = $this->host.':'.$this->port;
        }

        $url = $scheme . $host . $path;
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        $response = curl_exec($this->curl);
        if ($response){
            $info = explode("\r\n\r\n", $response);
            if (count($info) > 2) {
                [ $this->responseRedirectHeaders, $this->responseHeaders, $this->body ] = $info;
            } else {
                [ $this->responseHeaders, $this->body ] = $info;
            }
        }
        return true;
    }

    public function close() {
        curl_close($this->curl);
    }

}