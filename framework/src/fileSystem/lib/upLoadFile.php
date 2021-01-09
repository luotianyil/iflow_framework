<?php


namespace iflow\fileSystem\lib;

class upLoadFile extends fileSystem
{

    protected array $fileList = [];

    public function setFile($file): static
    {
        $this->fileList[] = new self($file);
        return $this;
    }

    public function getFileList(): array
    {
        return $this->fileList;
    }

    public function getFile($index)
    {
        return $this->fileList[$index];
    }
}