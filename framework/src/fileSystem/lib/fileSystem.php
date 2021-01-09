<?php


namespace iflow\fileSystem\lib;

use SplFileInfo;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class fileSystem extends SplFileInfo
{

    public FinfoMimeTypeDetector $finfoMimeTypeDetector;
    public \League\Flysystem\Filesystem $fileSystem;

    protected array $config = [];

    public function __construct($filename = '')
    {
        parent::__construct($filename);
    }

    public function getFileMine(): string
    {
        return $this->finfoMimeTypeDetector -> detectMimeType($this->getPathname(), '');
    }

    public function hash($algo = 'sha1'): string
    {
        return hash_file($algo, $this->getPathname());
    }

    public function md5(): string
    {
        return md5_file($this->getPathname());
    }

    // 删除目录
    public function deleteDirectory($path)
    {
        $this->fileSystem->deleteDirectory($path);
    }

    // 创建目录
    public function createDirectory(string $path, array $config = [])
    {
        $this->fileSystem -> createDirectory($path, $config);
    }

    // 读取文件
    public function read(): string
    {
        return $this->fileSystem -> read($this->getPathname());
    }

    // 写入文件流
    public function writeStream(string $path)
    {
        $this->fileSystem -> writeStream($path, $this->readStream());
    }

    // 读取文件流
    public function readStream()
    {
        return $this->fileSystem -> readStream($this->getPathname());
    }

    // 删除文件
    public function delete()
    {
        $this->fileSystem -> delete($this->getPathname());
    }

    // 移动文件
    public function move(string $savePath, array $config = [])
    {
        $this->fileSystem -> move($this->getPathname(), $savePath, $config);
    }

    // 复制文件
    public function copy(string $savePath, array $config = [])
    {
        $this->fileSystem -> copy($this->getPathname(), $savePath, $config);
    }
}