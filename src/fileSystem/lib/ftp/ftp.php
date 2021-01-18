<?php


namespace iflow\fileSystem\lib\ftp;


use iflow\fileSystem\lib\fileSystem;
use League\Flysystem\FTP\FtpConnectionOptions;
use League\Flysystem\FTP\FtpAdapter;
use League\Flysystem\FTP\FtpConnectionProvider;
use League\Flysystem\FTP\NoopCommandConnectivityChecker;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

class ftp extends fileSystem
{
    public function __construct($filename = '', array $config = [])
    {
        $adapter = new FtpAdapter(
            FtpConnectionOptions::fromArray($config),
            new FtpConnectionProvider(),
            new NoopCommandConnectivityChecker(),
            new PortableVisibilityConverter()
        );
        $this->fileSystem = new \League\Flysystem\Filesystem($adapter);
        parent::__construct($filename);
    }
}