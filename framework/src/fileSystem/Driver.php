<?php


namespace iflow\fileSystem;

use League\Flysystem\Filesystem;

/**
 * Class Driver
 * @package iflow\fileSystem
 * @mixin Filesystem
 */
abstract class Driver
{

    public function saveFile()
    {
    }
}