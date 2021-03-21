<?php


namespace iflow\template\lib;

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

    public function display(string $template, array $data = [])
    {
        // TODO: Implement display() method.
        $this->data = $data;
        $view_suffix = $this->config['view_suffix'] === '' ? '' : ".{$this->config['view_suffix']}";
        $this->file = $this->config['view_root_path'] . $template . $view_suffix;
        return $this->fetch();
    }

    public function fetch()
    {
        // TODO: Implement fetch() method.
        if ($this->exists()) {
            $storeFile = $this->getStoreFile();
            if (file_exists($storeFile)) return $this->send($storeFile);
            return $this->send($this->templateParser());
        } else {
            throw new \Exception('template file not exists');
        }
    }

    public function send($filePath = '')
    {
        extract($this->data, EXTR_OVERWRITE);
        include $filePath;
        return response() -> data('');
    }

    public function getContent(): string
    {
        $this->funcParser();
        return $this->content;
    }

    private function templateParser(): string
    {
        $this->content = file_get_contents($this->file);
        return $this->funcParser();
    }

}