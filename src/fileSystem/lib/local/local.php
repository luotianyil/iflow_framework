<?php


namespace iflow\fileSystem\lib\local;


use iflow\fileSystem\lib\fileSystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class local extends fileSystem
{

    public function __construct(string $filename = '', array $config = [])
    {
        $this->config = $config;
        $adapter = new LocalFilesystemAdapter(
             $this->config['rootPath'],
            PortableVisibilityConverter::fromArray($this->config)
        );
        $this->finfoMimeTypeDetector = app() -> make(FinfoMimeTypeDetector::class);
        $this->fileSystem = app() -> make(\League\Flysystem\Filesystem::class, [$adapter]);
        parent::__construct($this->config['rootPath'] .$filename);
    }

}