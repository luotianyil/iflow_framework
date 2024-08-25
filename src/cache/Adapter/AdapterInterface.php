<?php

namespace iflow\cache\Adapter;

interface AdapterInterface {

    public function initializer(array $config): AdapterInterface;

}