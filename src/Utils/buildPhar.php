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
        try {
            $this->phar -> buildFromDirectory($this->buildPhar -> app -> getDefaultRootPath());
            $this->phar -> setDefaultStub($this->config['bin'], $this->config['webindex']);

            $this->buildPhar -> Console -> outPut -> writeLine('build wait ...');
            $this->phar -> compressFiles(\Phar::GZ);

            if ($this->config['privatekey'] && file_exists($this->config['privatekey'])) {
                $private = openssl_get_privatekey(file_get_contents('private.pem'));
                $pkey = '';
                openssl_pkey_export($private, $pkey);
                $this->phar -> setSignatureAlgorithm(\Phar::OPENSSL, $pkey);
            }
            $this->phar -> stopBuffering();
            $this->buildPhar -> Console -> outPut -> writeLine('build success ...');
        } catch (\Throwable $exception) {
            $this->buildPhar -> Console -> outPut -> writeLine($exception -> getMessage());
        }
    }
}