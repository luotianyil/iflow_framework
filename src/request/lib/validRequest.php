<?php


namespace iflow\request\lib;


trait validRequest
{

    public string $request_method = '';

    /**
     * 是否为POST
     * @return bool
     */
    public function isPost(): bool
    {
        return strtoupper($this->request_method) === 'POST';
    }

    /**
     * 是否为GET
     * @return bool
     */
    public function isGet(): bool
    {
        return strtoupper($this->request_method) === 'GET';
    }

    /**
     * 是否为PUT
     * @return bool
     */
    public function isPut(): bool
    {
        return strtoupper($this->request_method) === 'PUT';
    }

    /**
     * 是否为DELETE
     * @return bool
     */
    public function isDelete(): bool
    {
        return strtoupper($this->request_method) === 'DELETE';
    }

    /**
     * 是否为OPTIONS
     * @return bool
     */
    public function isOptions(): bool
    {
        return strtoupper($this->request_method) === 'OPTIONS';
    }

    /**
     * 是否为AJAX
     * @return bool
     */
    public function isAjax(): bool
    {
        $value = $this->getHeader('HTTP_X_REQUESTED_WITH') ?: $this->getHeader('X-Requested-With');
        return $value && 'xmlhttprequest' == strtolower($value);
    }

    /**
     * 检测是否为HTTPS
     * @return bool
     */
    public function isHTTPS(): bool
    {
        if (isset($this -> server['https']) && ('1' == $this -> server['https'] || 'on' == strtolower($this -> server['https']))) {
            return true;
        } elseif (isset($this -> server['request_scheme']) && 'https' === $this -> server['request_scheme']) {
            return true;
        } elseif (443 === intval($this -> server['server_port'])) {
            return true;
        } elseif ('https' == $this -> getHeader('X_FORWARDED_PROTO')) {
            return true;
        }
        return false;
    }

}