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
        $this->userInfo = [
            'role' => 'admin'
        ];
        return $this;
    }

    public function getAuthList(): array
    {
        return $this->authList;
    }

    public function setAuthList(): self
    {
        $this->authList = [];
        return $this;
    }

    public function validateAuth(Request $request): bool
    {
        $this->authRole = explode('|', $this->authAnnotation -> role);
        $this->error = !in_array($this->userInfo['role'], $this->authRole);
        return $this->callback();
    }

    public function callback(): bool
    {
        if (!class_exists($this->authAnnotation -> callBack)) {
            return !$this->error;
        }
        return $this->authAnnotation -> app -> make($this->authAnnotation -> callBack) -> handle($this);
    }
}