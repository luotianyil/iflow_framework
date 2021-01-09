<?php


namespace iflow\fileSystem\lib\awsS3;

use iflow\fileSystem\lib\fileSystem;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Aws\S3\S3Client;

class awsS3 extends fileSystem
{

    public S3Client $s3;

    public function __construct(string $filename = '', array $config = [])
    {

        $this->config = $config;
        $this->s3 = new S3Client($config);
        $adapter = new AwsS3V3Adapter(
            $this->s3,
            $this->config['bucket'],
            $this->config['prefix'],
            $this->config['visibility'],
        );
        $this->fileSystem = new \League\Flysystem\Filesystem($adapter);

        parent::__construct($filename);
    }
}