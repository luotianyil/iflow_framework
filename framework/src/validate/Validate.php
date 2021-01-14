<?php


namespace iflow\validate;

use iflow\validate\lib\validateBase;

class Validate extends validateBase
{

    protected array $rule = [];
    protected array $validateData = [];
    protected array $message = [];

    public function rule(array $rule, array $message = []): static
    {
        $this->rule = $rule;
        $this->message = $message;
        return $this;
    }

    public function message(array $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function check(array $data): static
    {
        $this->validateData = $data;
        return $this->validate();
    }

    protected function validate(): static
    {
        foreach ($this->rule as $key => $value) {
            $key = explode(':', $key);
            $this->error[$key[0]] = match ($value instanceof \Closure) {
                true => call_user_func($value, $this->validateData[$key[0]] ?? ''),
                default => $this->explodeValidateRule($key, $value)
            };
        }
        return $this;
    }

    protected function explodeValidateRule($key, $rule)
    {
        $rule = explode('|', $rule);
        $error = [];
        foreach ($rule as $key => $value) {
            $value = explode(':', $value);
            if (strtolower($value[0]) === 'validatefunc') {
                $error[] = $this->validateFunc($key[0], $value[1]);
            } elseif (call_user_func([$this, $value[0]], ...[$this->validateData[$key[0]] ?? null, $value[1]])) {
                $error[] = $this->message[$value[0].$value[1]] ?? ($key[1] ?? $key[0]. $value);
            }
        }
        return $error;
    }

    protected function validateFunc($key, $method)
    {
        return call_user_func([$this, $method], [$this->validateData[$key], $this->validateData]);
    }

}