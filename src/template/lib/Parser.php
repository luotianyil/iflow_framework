<?php


namespace iflow\template\lib;

use iflow\exception\lib\HttpException;
use iflow\exception\lib\HttpResponseException;
use iflow\Response;

class Parser extends tag implements TemplateParser
{

    public function config(array $config = [])
    {
        // TODO: Implement config() method.
        $this->config = array_merge($this->config, $config);
    }

    public function exists()
    {
        // TODO: Implement exists() method.
        return file_exists($this->file);
    }

    public function display(string $template, array $data = []): Response | bool
    {
        // TODO: Implement display() method.
        $this->data = $data;
        $view_suffix = $this->config['view_suffix'] === '' ? '' : ".{$this->config['view_suffix']}";
        $this->file = $this->config['view_root_path'] . $template . $view_suffix;
        try {
            return $this->fetch();
        } catch (\Exception $e) {
            return response() -> notFount($e -> getMessage());
        }
    }

    public function fetch(): Response
    {
        // TODO: Implement fetch() method.
        if ($this->exists()) {
            $storeFile = $this->getStoreFile();
            if (file_exists($storeFile)) {
                if ($this->config['cache_enable']) return $this->send($storeFile);
                // 删除缓存文件
                unlink($storeFile);
            }
            return $this->send($this->templateParser());
        } else {
            throw new HttpException('template file not exists');
        }
    }

    public function send($filePath = ''): Response
    {
        ob_start();
        extract($this->data, EXTR_OVERWRITE);
        include $filePath;
        $info = ob_get_contents();
        ob_end_clean();
        return response() -> data($info);
    }

    public function getContent(): string
    {
        $this->funcParser();
        return $this->content;
    }

    protected function templateParser(): string
    {
        $this->content = file_get_contents($this->file);
        if ($this->content === '') {
            throw new HttpResponseException(
                message() -> nodata('Template Content is Empty')
            );
        }
        if ($this->FileIsTemplateLibrary()) {
            throw new HttpResponseException(
                message() -> nodata('TemplateFile is templateLibrary')
            );
        }
        return $this->funcParser();
    }
}