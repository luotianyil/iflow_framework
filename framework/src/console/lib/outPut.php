<?php


namespace iflow\console\lib;


class outPut
{
    public function __construct(protected $outFile = null)
    {}

    public function write($data): static
    {
        fwrite($this->outFile, $data);
        return $this;
    }

    public function outPutWrite()
    {
        fflush($this->outFile);
    }

}