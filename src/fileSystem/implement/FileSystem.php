<?php


namespace iflow\fileSystem\implement;

use SplFileInfo;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class FileSystem extends SplFileInfo {

    public ?FinfoMimeTypeDetector $finfoMimeTypeDetector = null;
    public \League\Flysystem\Filesystem $fileSystem;

    protected array $config = [];

    public function __construct($filename = '') {
        parent::__construct($filename);
    }

    public function getFileMine(): string {
        $this->finfoMimeTypeDetector = $this->finfoMimeTypeDetector ?: app() -> make(FinfoMimeTypeDetector::class);
        return $this->finfoMimeTypeDetector -> detectMimeType($this->getPathname(), '');
    }

    public function hash($algo = 'sha1'): string {
        return hash_file($algo, $this->getPathname());
    }

    public function md5(): string {
        return md5_file($this->getPathname());
    }

    /**
     * 删除目录
     * @param $path
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function deleteDirectory($path):  void {
        $this->fileSystem->deleteDirectory($path);
    }

    /**
     * 创建目录
     * @param string $path
     * @param array $config
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function createDirectory(string $path, array $config = []): void {
        $this->fileSystem -> createDirectory($path, $config);
    }

    /**
     * 读取文件
     * @return string
     * @throws \League\Flysystem\FilesystemException
     */
    public function read(): string {
        return $this->fileSystem -> read($this->getPathname());
    }

    /**
     * 写入文件流
     * @param string $path
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function writeStream(string $path): void {
        $this->fileSystem -> writeStream($path, $this->readStream());
    }

    /**
     * 读取文件流
     * @return resource
     * @throws \League\Flysystem\FilesystemException
     */
    public function readStream() {
        return $this->fileSystem -> readStream($this->getPathname());
    }

    /**
     * 删除文件
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function delete(): void {
        $this->fileSystem -> delete($this->getPathname());
    }

    /**
     * 移动文件
     * @param string $savePath
     * @param array $config
     * @return mixed
     * @throws \League\Flysystem\FilesystemException
     */
    public function move(string $savePath, array $config = []): mixed {
        $this->fileSystem -> move($this->getPathname(), $savePath, $config);
        return true;
    }

    /**
     * 复制文件
     * @param string $savePath
     * @param array $config
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function copy(string $savePath, array $config = []): void {
        $this->fileSystem -> copy($this->getPathname(), $savePath, $config);
    }
}