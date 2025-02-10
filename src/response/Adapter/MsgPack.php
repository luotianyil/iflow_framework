<?php

namespace iflow\response\Adapter;

use iflow\Response;

class MsgPack extends Response {

    public function __construct(mixed $data = [], int $code = 200) {
        $this->contentType = 'application/msgpack';
        $this->init($data, $code);
    }

    /**
     * msgpack 序列化
     * @param mixed $data
     * @return mixed
     * @throws \Throwable
     */
    public function output(mixed $data): mixed {
        try {

            if (!class_exists(\MessagePack::class)) {
                throw new \Exception("MessagePack library not loaded");
            }

            return (new \MessagePack(false)) -> pack($data);
        } catch (\Exception $e) {
            if ($e -> getPrevious()) throw $e -> getPrevious();
            throw $e;
        }
    }

}