<?php


namespace iflow\response\lib;

use iflow\Response;

class File extends Response
{
    protected array $mimeType = [
        'js' => 'text/javascript',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'shtml' => 'text/html',
    ];

    public string $contentType = 'text/html';
    public function __construct(string $data = '', int $code = 200)
    {
        $this->init($data, $code);
    }

    /**
     * 返回file
     * @param $data
     * @return bool
     */
    public function output($data): bool
    {
        if (file_exists($data)) {
            $finfo    = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $data);
            finfo_close($finfo);
            $ext = pathinfo($data, PATHINFO_EXTENSION);
            if (isset($this->mimeType[$ext])) $mimetype = $this->mimeType[$ext];
            return $this->contentType($mimetype) -> end($data);
        } else $this->notFount();
        return false;
    }

    public function send(): bool
    {
        return $this->output($this->data);
    }

    public function end($data): bool
    {
        $this->setResponseHeader();
        return $this->response -> sendFile($data);
    }

}