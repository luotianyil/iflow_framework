<?php


namespace iflow\fileSystem;


use iflow\Container;
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
        $this->fileList = app(FileList::class);
        return $this;
    }

    public function create($file)
    {
        $this->config = config('fileSystem');
        $this->config = $this->config['disks'][$this->config['default']];
        $class = 'iflow\\fileSystem\\lib\\'.$this->config['type'].'\\'.$this->config['type'];
        return Container::getInstance()->invokeClass($class, [$file, $this->config]);
    }
}