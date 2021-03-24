<?php


namespace iflow\auth\lib;

use iflow\Request;

class authHandle
{

    protected array $userInfo = [];

    // 自定义角色
    protected array $authRoles = [];

    public array $router = [];
    public bool $error = false;

    public function __construct(
        protected authAnnotation $authAnnotation
    ){}

    public function getUserInfo(): array
    {
        return $this->userInfo;
    }

    public function setUserInfo(): static
    {
        $token_key = request() -> params('Authorization');
        $token_key = $token_key ?: request() -> getHeader('Authorization');
        if ($token_key) $this->userInfo = session($token_key);
        return $this;
    }

    public function getAuthRoles(): array
    {
        return $this->authRoles;
    }

    public function setAuthRoles(): static
    {
        $this->authRoles = explode('|', $this->authAnnotation -> role);
        return $this;
    }

    public function validateAuth(Request $request): static
    {
        if (empty($this->userInfo['role'])) {
            $this->error = true;
        } else {
            $this->authRoles = array_merge(explode('|', $this->authAnnotation -> role), $this->authRoles);
            if ($this->authAnnotation -> role !== '*') $this->error = !in_array($this->userInfo['role'], $this->authRoles);
        }
        return $this;
    }

    public function callback(): bool
    {
        if (!class_exists($this->authAnnotation -> callBack)) {
            return !$this->error;
        }
        return $this->authAnnotation -> app -> make($this->authAnnotation -> callBack) -> handle($this);
    }
}