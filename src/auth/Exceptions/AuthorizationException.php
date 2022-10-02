<?php


namespace iflow\auth\Exceptions;

use iflow\exception\Adapter\HttpResponseException;
use iflow\Response;

class AuthorizationException extends HttpResponseException
{
    public function __construct(mixed $message = 'Unauthorized', \Exception $previous = null, array $headers = [], $code = 0) {
        $message = $message instanceof Response
            ? $message
            : message() -> unauthorized_error(is_bool($message) ? 'Unauthorized' : $message);
        parent::__construct($message);
    }
}
