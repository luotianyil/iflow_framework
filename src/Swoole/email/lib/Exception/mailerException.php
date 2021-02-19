<?php


namespace iflow\Swoole\email\lib\Exception;


class mailerException extends \Exception
{

    public function getError()
    {
        return $this->getMessage();
    }

}