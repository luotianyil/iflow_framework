<?php


namespace iflow\cache\lib;


class File
{

    protected array $config = [];

    public function initializer(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    protected function getStorePath()
    {
        return $this->config['path'];
    }

    public function set(string $name, array $data)
    {
        !is_dir($this->config['path']) && mkdir($this->config['path'], 0755, true);
        $file = $this->config['path']. $name. '.php';
        $fileStream = fopen($file, "w+");
        $old_data = $this->get($name);

        $data = $old_data ? array_replace_recursive($old_data, $data) : $data;
        $data = serialize($data);

        fwrite($fileStream, gzcompress($data));
        return fclose($fileStream);
    }

    public function get(string $name)
    {
        $file = $this->getStorePath() . $name. '.php';
        if (file_exists($file))
            return unserialize(gzuncompress(file_get_contents($file)));
        return [];
    }

    public function delete(string $name): bool
    {
        return @unlink($this->getStorePath() . $name . '.php');
    }
}