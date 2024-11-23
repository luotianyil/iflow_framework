<?php

namespace iflow\swoole\implement\Tools\Pool\Interfaces;

interface PoolInterface {
    
    
    public function initializer(): PoolInterface;


    public function destroy(): mixed;

}