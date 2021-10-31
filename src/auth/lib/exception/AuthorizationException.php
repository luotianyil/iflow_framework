<?php


namespace iflow\auth\lib\exception;


use iflow\exception\lib\HttpResponseException;
use iflow\Response;

class AuthorizationException extends HttpResponseException
{
    public function __construct(mixed $message = 'Unauthorized', \Exception $previous = null, array $headers = [], $code = 0) {
        parent::__construct(
            $message instanceof Response ?
                $message : message() -> unauthorized_error($message)
        );
    }
}
