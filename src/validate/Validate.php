<?php


namespace iflow\validate;

use iflow\validate\Adapter\ValidateBase;

class Validate extends ValidateBase
{

    protected array $rule = [];
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
        if (count($this->validateData) === 0) {
            $this->error[] = "validateData is empty";
        } else {
            foreach ($this->rule as $key => $value) {
                $key = explode('|', $key);
                $error = match ($value instanceof \Closure) {
                    true => $this->runClosure($value, $key),
                    default => $this->explodeValidateRule($key, $value)
                };
                if (count($error) > 0) $this->error[$key[0]] = $error;
            }
        }
        return $this;
    }

    protected function explodeValidateRule($key, $rule): array
    {
        $rule = explode('|', $rule);
        $error = [];
        foreach ($rule as $value) {
            $value = explode(':', $value);
            $value[1] = $value[1] ?? null;

            if (strtolower($value[0]) === 'validatefunc') {
                $err = $this->validateFunc($key[0], $value[1]);
                if ($err !== true) $error[] = $err;
            } elseif (!call_user_func([$this, $value[0]], $this->validateData[$key[0]] ?? null, $value[1] ?? null)) {
                $error[] = $this->message[$key[0]. "." . $value[0]] ?? ($key[1] ?? $key[0]). implode(" ", $value);
            }
        }
        return $error;
    }

    protected function validateFunc($key, $method)
    {
        if (method_exists($this, $method))
            return call_user_func([$this, $method], $this->validateData[$key] ?? null, $this->validateData);
        return true;
    }

    protected function runClosure(\Closure $closure, $key): bool|array
    {
        $err = call_user_func($closure, $this->validateData[$key[0]] ?? null, $this->validateData);
        if ($err !== true) {
            return is_array($err) ? $err : [$err];
        }
        return [];
    }

}