<?php


namespace iflow\auth\lib;

use iflow\Request;

class authHandle
{

    protected array $userInfo = [];
    protected array $authList = [];
    protected array $authRole = [];

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
        $this->userInfo = session($token_key);
        return $this;
    }

    public function getAuthList(): array
    {
        return $this->authList;
    }

    public function setAuthList(): static
    {
        $this->authList = [];
        return $this;
    }

    public function validateAuth(Request $request): static
    {
        if (empty($this->userInfo['role'])) {
            $this->error = true;
        } else {
            $this->authRole = explode('|', $this->authAnnotation -> role);
            $this->error = !in_array($this->userInfo['role'], $this->authRole);
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