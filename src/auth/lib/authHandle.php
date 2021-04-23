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

    /**
     * 设置请求用户信息
     * @return $this
     */
    public function setUserInfo(): static
    {
        $this->userInfo = session('userInfo');
        return $this;
    }

    public function getAuthRoles(): array
    {
        return $this->authRoles;
    }

    /**
     * 设置当前验证器 角色
     * @return $this
     */
    public function setAuthRoles(): static
    {
        $this->authRoles = explode('|', $this->authAnnotation -> role);
        return $this;
    }

    public function validateAuth(Request $request): static
    {
        if (!$this->userInfo) {
            $this->error = true;
        } else {
            $this->userInfo['role'] =
                is_string($this->userInfo['role']) ? [$this->userInfo['role']]
                    : $this->userInfo['role'];

            if (empty($this->userInfo['role'])) {
                $this->error = true;
            } else {
                $this->authRoles = array_merge(explode('|', $this->authAnnotation -> role), $this->authRoles);
                if ($this->authAnnotation -> role !== '*') {
                    $this->error = count(array_intersect($this->userInfo['role'], $this->authRoles)) === 0;
                }
            }
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