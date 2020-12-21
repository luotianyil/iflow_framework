<?php


namespace iflow\fileSystem;


use iflow\fileSystem\lib\FileList;

/**
 * Class File
 * @package iflow\fileSystem
 * @property FileList $fileList
 */
class File
{
    public function initializer()
    {
        $this->fileList = app(FileList::class);
        return $this;
    }
}