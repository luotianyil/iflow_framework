<?php

namespace iflow\swoole\interfaces;

interface ServicesInterface {

    public function start();

    public function stop();

    public function reload();

}