<?php


namespace iflow\console\lib;


class outPut {
    public function __construct(protected $outFile = null) {
        $banner = config('banner');
        if (isset($banner[0])) fwrite($this->outFile, $banner[0].PHP_EOL);
    }

    public function write($data): static {
        fwrite($this->outFile, $data);
        return $this;
    }

    public function writeLine(string $msg) {
        $this -> write($msg.PHP_EOL)->outPutWrite();
    }

    public function outPutWrite() {
        fflush($this->outFile);
    }
}