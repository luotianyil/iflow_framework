<?php

namespace iflow\swoole\implement\Server\GRpc;

use iflow\initializer\Error;
use iflow\swoole\implement\Server\Http\Service as HttpService;

class Service extends HttpService {

    protected array $grpcContentType = [
        'application/grpc',
        'application/grpc+proto'
    ];

    public function onRequest(object $request, object $response): mixed {

        try {
            if (!in_array($request -> header['content-type'], $this->grpcContentType)) {
                return throw new \Exception('Invalid then Content-Type');
            }

            return parent::onRequest($request, $response); // TODO: Change the autogenerated stub
        } catch (\Exception $exception) {
            return app(Error::class) -> appHandler($exception);
        }
    }


}