<?php


namespace iflow\fileSystem\lib;

class upLoadFile extends fileSystem
{

    public function __construct($filename, bool $checkPath = true)
    {
        if ($checkPath && !is_file($filename)) {
            throw new \Exception(sprintf('The file "%s" does not exist', $filename));
        }
        parent::__construct($filename);
    }

    public function save()
    {
    }


    public function md5File()
    {
    }

    public function hashFile()
    {
    }

}