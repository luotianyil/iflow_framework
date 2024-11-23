<?php

namespace iflow\validate;

use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\validate\Adapter\FilterValidator;
use iflow\validate\Adapter\RegexValidator;

class Validator {

    use RegexValidator, FilterValidator;

    protected array $error = [];

    protected array $validator = [];

    protected array $rule = [];

    protected array $message = [];

    protected function required($value): bool {
        return !is_null($value);
    }


    /**
     * 确认字段
     * @param $value
     * @param $confirmFiled
     * @return bool
     */
    public function confirm($value, $confirmFiled): bool {
        if (isset($this->validateData[$confirmFiled])) {
            return $value === $this->validateData[$confirmFiled];
        }
        return false;
    }


    public function rule(array $rule, array $message = []): Validator {
        $this->rule = $rule;
        $this->message = $message;
        return $this;
    }

    public function message(array $message): Validator {
        $this->message = $message;
        return $this;
    }

    public function check(array $data): static {
        $this->validator = $data;
        return $this->validator();
    }


    /**
     * 数据验证
     * @param array $data
     * @param array $rule
     * @param array $message
     * @return $this
     */
    public function validator(array $data = [], array $rule = [], array $message = []): Validator {

        $this->validator = $data ?: $this->validator;
        $this->rule = $rule ?: $this->rule;
        $this->message = $message ?: $this->message;

        if (empty($data)) {
            $this->error[] = 'validator data empty';
            return $this;
        }

        foreach ($this->rule as $key => $value) {
            $key = explode('|', $key);
            $error = match ($value instanceof \Closure) {
                true => $this->executeValidateClosure($value, $key),
                default => $this->queryValidateRule($key, $value)
            };
            if (count($error) > 0) $this->error[$key[0]] = $error;
        }

        return $this;
    }


    /**
     * 查询验证类型
     * @param $key
     * @param $rule
     * @return array
     * @throws InvokeClassException
     * @throws InvokeFunctionException|ValueException
     */
    protected function queryValidateRule($key, $rule): array {
        $rule = explode('|', $rule);
        $error = [];

        foreach ($rule as $value) {
            $value = explode(':', $value);
            $value[1] = $value[1] ?? null;

            // validate:method
            if (strtolower($value[0]) === 'validate') {
                $err = $this->executeValidateClosure($value[1], $key);
                if ($err !== true) $error[] = $err;
            } elseif (!$this -> executeSelfValidateMethod($value[0], $key[0], $value[1] ?? null)) {
                $error[] = $this->message[$key[0]. "." . $value[0]] ?? ($key[1] ?? $key[0]). implode(" ", $value);
            }
        }
        return $error;
    }

    /**
     * 执行自定义验证函数
     * @param string $method
     * @param string|int $key
     * @param mixed $validateRuleArgs
     * @return mixed
     * @throws InvokeClassException
     * @throws InvokeFunctionException|ValueException
     */
    protected function executeSelfValidateMethod(string $method, string|int $key, mixed $validateRuleArgs): mixed {

        if (method_exists($this, $method))
            return app() -> invoke([ $this, $method ], [ $this->validator[$key] ?? null, $validateRuleArgs, $this->validator ]);


        throw new ValueException("validateRule {$method} with a non-existent");
    }

    /**
     * @param \Closure|string|array $closure
     * @param $key
     * @return bool|array
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    protected function executeValidateClosure(\Closure|string|array $closure, $key): bool|array {

        if (is_string($closure) && method_exists($this, $closure)) {
            $closure = [ $this, $closure ];
        }

        $err = app() -> invoke($closure, [ $this->validator[$key[0]] ?? null, $this->validator ]);
        if ($err !== true) {
            return is_array($err) ? $err : [ $err ];
        }
        return [];
    }

    public function getError(): array {
        return $this->error;
    }

    /**
     * 获取单条异常数据
     * @return mixed
     */
    public function first(): mixed {

        if (empty($this->error)) return null;

        foreach ($this->error as $err) {
            foreach ($err as $errItem) return $errItem;
        }

        return null;
    }

}