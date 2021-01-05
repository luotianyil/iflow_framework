<?php


namespace iflow\fileSystem\lib;

use SplFileInfo;


class fileSystem extends SplFileInfo
{

    public function mkdir(string $file = '', int $chmod = 0666): bool
    {
        return mkdir($file, $chmod & ~umask());
    }

    public function saveFile(string $path, $file, string $name, array $options = [])
    {
        $stream = fopen($file->getRealPath(), 'r');
        $path = trim($path . '/' . $name, '/');

        $result = $this->putStream($path, $stream, $options);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result ? $path : false;
    }

}