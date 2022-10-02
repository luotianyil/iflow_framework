<?php


namespace iflow\fileSystem;


use iflow\Container\Container;
use iflow\fileSystem\lib\FileList;

/**
 * Class File
 * @package iflow\fileSystem
 * @property FileList $fileList
 */
class File
{

    protected array $config = [];

    public function initializer()
    {
        $this->fileList = Container::getInstance() -> make(FileList::class);
        return $this;
    }

    public function create($file)
    {
        $this->config = config('fileSystem');
        $this->config = $this->config['disks'][$this->config['default']];
        $class = 'iflow\\fileSystem\\implement\\'.$this->config['type'].'\\'.$this->config['type'];
        return Container::getInstance()->invokeClass($class, [$file, $this->config]);
    }

    public function readFile($path): string|\Generator
    {
        if (file_exists($path)) {
            $fp = fopen($path, "r");
            while (!feof($fp)) yield fgets($fp);
            fclose($fp);
        }
        return "";
    }
}