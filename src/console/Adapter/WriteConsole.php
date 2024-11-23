<?php

namespace iflow\console\Adapter;

class WriteConsole {

    public function __construct(protected $outFile) {
        $banner = config('banner');
        if (isset($banner[0])) fwrite($this->outFile, $banner[0].PHP_EOL);
    }

    public function write($data): WriteConsole {
        fwrite($this->outFile, $data);
        return $this;
    }

    public function writeLine(string $msg): void {
        $this -> write($msg.PHP_EOL)->outPutWrite();
    }

    public function outPutWrite(): void {
        fflush($this->outFile);
    }

}