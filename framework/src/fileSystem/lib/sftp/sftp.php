<?php


namespace iflow\fileSystem\lib\sftp;


use iflow\fileSystem\lib\fileSystem;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class sftp extends fileSystem
{
    public function __construct(string $filename = '', array $config = []){
        $this->config = $config;
        $this->fileSystem = new \League\Flysystem\Filesystem(new SftpAdapter(
            new SftpConnectionProvider(...array_values($this->config['options'])),
            $this->config['rootPath'],
            PortableVisibilityConverter::fromArray($this->config)
        ));
        parent::__construct($this->config['rootPath'] .$filename);
    }
}