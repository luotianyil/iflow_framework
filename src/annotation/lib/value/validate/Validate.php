<?php


namespace iflow\annotation\lib\value\validate;


use iflow\annotation\lib\value\Exception\valueException;

class Validate
{
    public function handle(array $validate)
    {
        try {
            validate($validate['rule'], $validate['data'], $validate['message']);
        } catch (\Exception $exception) {
            throw (new valueException()) -> setError(message() -> parameter_error($exception -> getMessage()));
        }
    }
}