<?php


namespace iflow\annotation\lib\value\Exception;


class valueException extends \Exception
{
    protected mixed $error = "";

    /**
     * @param mixed $error
     * @return static
     */
    public function setError(mixed $error): static
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getError(): mixed
    {
        return $this->error;
    }
}