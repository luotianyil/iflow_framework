<?php


namespace iflow\validate\Adapter;


use iflow\fileSystem\implement\fileSystem;

class ValidateBase {

    protected array $defaultRegex = [
        'alpha'       => '/^[A-Za-z]+$/',
        'alphaNum'    => '/^[A-Za-z0-9]+$/',
        'alphaDash'   => '/^[A-Za-z0-9\-\_]+$/',
        'chs'         => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'chsAlpha'    => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',
        'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u',
        'chsDash'     => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u',
        'mobile'      => '/^1[3-9]\d{9}$/',
        'zip'         => '/\d{6}/',
    ];

    protected array $filter = [
        'email'   => FILTER_VALIDATE_EMAIL,
        'ip'      => [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
        'integer' => FILTER_VALIDATE_INT,
        'url'     => FILTER_VALIDATE_URL,
        'macAddr' => FILTER_VALIDATE_MAC,
        'float'   => FILTER_VALIDATE_FLOAT,
    ];

    protected array $error = [];

    protected array $validateData = [];

    public function min(mixed $value, $rule = 0): bool
    {
        return $this->getLength($value) >= $rule;
    }

    public function max(mixed $value, $rule = 0): bool
    {
        return $this->getLength($value) <= $rule;
    }

    public function email($value): bool
    {
        return $this->filter($value, $this->filter['email']);
    }

    public function ip($value, $rule = 'ipv4'): bool
    {
        if (!in_array($rule, ['ipv4', 'ipv6'])) $rule = 'ipv4';
        return $this->filter($value, [FILTER_VALIDATE_IP, 'ipv6' == $rule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4]);
    }

    public function integer($value): bool
    {
        return $this->filter($value, $this->filter['integer']);
    }

    public function url($value): bool
    {
        return $this->filter($value, $this->filter['url']);
    }

    public function macAddr($value): bool
    {
        return $this->filter($value, $this->filter['macAddr']);
    }

    public function float($value): bool
    {
        return $this->filter($value, $this->filter['float']);
    }

    protected function getLength(mixed $value): int
    {
        if (is_numeric($value)) return intval($value);
        if ($value instanceof fileSystem) {
            $length = $value -> getSize();
        } else {
            $length = is_array($value) ? count($value) : mb_strlen($value, 'utf8');
        }
        return $length;
    }

    protected function required($value): bool
    {
        return !is_null($value);
    }

    public function getError(): array
    {
        return $this->error;
    }

    /**
     * 获取单条异常数据
     * @return mixed
     */
    public function first(): mixed {

        foreach ($this->error as $err) {
            foreach ($err as $errItem) return $errItem;
        }

        return null;
    }

    public function regex($value, $rule): bool
    {
        if (isset($this->defaultRegex[$rule])) {
            $rule = $this->defaultRegex[$rule];
        }

        if (is_string($rule) && !str_starts_with($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            $rule = '/^' . $rule . '$/';
        }
        return is_scalar($value) && 1 === preg_match($rule, (string) $value);
    }

    public function filter($value, $rule): bool
    {
        $param = null;
        if (is_string($rule)) {
            [$rule, $param] = explode(',', $rule);
        } elseif (is_array($rule)){
            $param = $rule[1] ?? null;
            $rule = $rule[0];
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param ?: []);
    }

    // 确认字段
    public function confirm($value, $rule): bool
    {
        if (isset($this->validateData[$rule])) {
            return $value === $this->validateData[$rule];
        }
        return false;
    }
}