<?php


namespace iflow\validate\lib;


use iflow\fileSystem\lib\fileSystem;

class validateBase
{

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

    protected array $alias = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', 'same' => 'eq',
    ];

    protected array $error = [];

    public function min(mixed $value, int $rule = 0): bool
    {
        return $this->getLength($value) >= $rule;
    }

    public function max(mixed $value, int $rule = 0): bool
    {
        return $this->getLength($value) <= $rule;
    }

    protected function getLength(mixed $value): int
    {
        if ($value instanceof fileSystem) {
            $length = $value -> getSize();
        } else {
            $length = is_array($value) ? count($value) : mb_strlen($value, 'utf8');
        }
        return $length;
    }

    protected function required($value): bool
    {
        return is_null($value) || $value === '';
    }

    public function getError(): array
    {
        return $this->error;
    }

    public function find()
    {
        foreach ($this->error as $key => $value) {
            foreach ($value as $k => $v) return $v;
        }
        return null;
    }

    public function regex($value, $rule): bool
    {
        if (isset($this->defaultRegex[$rule])) {
            $rule = $this->defaultRegex[$rule];
        }

        if (is_string($rule) && 0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            $rule = '/^' . $rule . '$/';
        }
        return is_scalar($value) && 1 === preg_match($rule, (string) $value);
    }

}