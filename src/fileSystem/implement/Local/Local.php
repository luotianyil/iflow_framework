<?php


namespace iflow\fileSystem\implement\Local;


use iflow\fileSystem\implement\FileSystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class Local extends FileSystem {

    public function __construct(string $filename = '', array $config = []) {
        $this->config = $config;
        $adapter = new LocalFilesystemAdapter(
             $this->config['rootPath'],
            PortableVisibilityConverter::fromArray($this->config)
        );

        $this->finfoMimeTypeDetector = app(FinfoMimeTypeDetector::class);
        $this->fileSystem = app(\League\Flysystem\Filesystem::class, [ $adapter ]);
        parent::__construct($this->config['rootPath'] .$filename);
    }

}