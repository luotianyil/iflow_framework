<?php


namespace iflow\http\lib;

class Client
{

    protected object $curl;

    public string $method = 'GET';
    public array $headers = [];
    public array $data = [];

    public string $responseHeaders = '';
    public string $body = '';

    public function __construct(public string $host = '', public int $port = 0, public bool $isSSL = false)
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
    }

    public function set($option = [])
    {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $option['timeout']??30);
    }

    /**
     * @param string $method
     * @return static
     */
    public function setMethod(string $method): static
    {
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
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    /**
     * @param array $data
     * @return static
     */
    public function setData(array $data): static
    {
        if ($this->method === 'POST')  curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    public function setCookies(string $cookies): static
    {
        curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);
        return $this;
    }

    /**
     * 设置请求证书
     * @param string $keyFile
     * @param string $certFile
     * @return $this
     */
    public function setSSL($keyFile = "", $certFile = ""): static
    {
        if (file_exists($keyFile) && file_exists($certFile)) {
            curl_setopt($this->curl, CURLOPT_SSLKEY, $keyFile);
            curl_setopt($this->curl, CURLOPT_SSLCERT, $certFile);
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        } else {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        return $this;
    }

    public function execute($path = ''): bool
    {
        if ($this->curl === null) return false;
        $scheme = $this->isSSL ? 'https://' : 'http://';
        $url = $scheme . $this->host . $path;
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        $response = curl_exec($this->curl);
        if ($response){
            [$this->responseHeaders, $this->body] = explode("\r\n\r\n", $response);
        }
        return true;
    }

    public function close()
    {
        curl_close($this->curl);
    }

}