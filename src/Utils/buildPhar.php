<?php


namespace iflow\Utils;


class buildPhar
{

    protected \Phar $phar;

    public function __construct(protected array $config, protected \iflow\command\buildPhar $buildPhar)
    {
        $this->phar = new \Phar($this->config['out']);
    }

    public function build()
    {
        $this->phar -> buildFromDirectory($this->buildPhar -> app -> getDefaultRootPath());
        $this->phar -> setDefaultStub($this->config['bin'], $this->config['webindex']);

        $this->buildPhar -> Console -> outPut -> writeLine('build wait ...');
        $this->phar -> compressFiles(\Phar::GZ);
        $this->phar -> setSignatureAlgorithm(\Phar::OPENSSL);
        $this->buildPhar -> Console -> outPut -> writeLine('build end ...');
    }
}