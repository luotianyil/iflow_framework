<?php


namespace iflow\response\lib;


use iflow\Response;

class File extends Response
{

    protected array $contentTypes = [
        'js' => 'text/javascript; charset=UTF-8',
        'css' => 'text/css'
    ];

    public string $contentType = 'text/html; charset=utf-8';
    public function __construct(string $data = '', int $code = 200)
    {
        $this->init($data, $code);
    }

    /**
     * 返回file
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function output($data): bool
    {
        if (file_exists($data)) {
            $finfo    = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $data);
            finfo_close($finfo);
            $ext = pathinfo($data, PATHINFO_EXTENSION);
            if (in_array($ext, $this->contentTypes)) $mimetype = $this->contentTypes[$ext];
            return $this->contentType($mimetype)->response -> sendfile($data);
        } else $this->notFount();
        return false;
    }

    public function send()
    {
        return $this->output($this->data);
    }

}