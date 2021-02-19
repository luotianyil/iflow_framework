<?php


namespace iflow\Swoole\email\lib\Message;


use iflow\fileSystem\lib\fileSystem;

trait attachment
{
    protected array $attachment = [];
    protected string $boundary = "";
    protected string $charSet = "utf-8";

    public function addAttachment(string $filename = '', string $filePath = '', ?string $mime = null): static
    {
        $this->attachment[] = [
            'fileName' => $filename !== '' ? $filename : basename($filePath),
            'path' => $filePath,
            'mime' => $mime
        ];
        return $this;
    }

    protected function attachmentToBody($body): string
    {
        $this -> boundary = '----='.uniqid('_mailer');
        return $this->setAttachHeader($body);
    }

    protected function setAttachHeader($body): string
    {
        $body   = base64_encode($body);
        $body   = str_replace("\r\n" . '.', "\r\n" . '..', $body);
        $body   = substr($body, 0, 1) == '.' ? '.' . $body : $body;

        $headers []    =  "Content-Type: multipart/mixed;boundary=\"{$this -> boundary}\"\r\n";
        $headers []    =  '--' . $this -> boundary;
        $headers []    =  'Content-Type: text/html;charset="'.$this->charSet.'"';
        $headers []    =  'Content-Transfer-Encoding: base64'. "\r\n";
        $headers []    =  '';
        $headers []    =  $body . "\r\n";
        foreach ($this->attachment as $file) {
            $this->genAttach($file, $headers);
        }
        $headers[] = "--" . $this -> boundary . "--";
        return str_replace("\r\n" . '.', "\r\n" . '..', trim(implode("\r\n", $headers)));
    }


    protected function genAttach($file, &$headers)
    {
        if (file_exists($file['path'])) {
            $fileSystem = new fileSystem($file['path']);
            $mimetype = !$file['mime'] ? $fileSystem -> getFileMine() : $file['mime'];
            $content = chunk_split(base64_encode(file_get_contents($file['path'])));
            $headers[] = "--". $this -> boundary;
            $headers[] = 'Content-type: ' . $mimetype . '; name="' . $file['fileName'] . '"';
            $headers[] = 'Content-disposition: attachment; filename="' . $file['fileName'] . '"';
            $headers[] = 'Content-Transfer-Encoding: base64'."\r\n";
            $headers[] = $content."\r\n";
        }
    }

}