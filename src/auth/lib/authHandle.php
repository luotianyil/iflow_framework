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
            if ($this->authAnnotation -> role !== '*') {
                $this->userInfo['role'] =
                    is_string($this->userInfo['role']) ? [$this->userInfo['role']]
                        : $this->userInfo['role'];

                if (empty($this->userInfo['role'])) {
                    $this->error = true;
                } else {
                    $this->authRoles = array_merge(explode('|', $this->authAnnotation -> role), $this->authRoles);
                    $this->error = count(array_intersect($this->userInfo['role'], $this->authRoles)) === 0;
                }
            }
        }
        return $this;
    }

    public function callback(): bool
    {
        $class = explode('@', $this->authAnnotation -> callBack);
        $method = '';
        if (count($class) > 1) [$class, $method] = $class;
        else $class = $class[0];
        // 回调参数
        if (!class_exists($class)) {
            return !$this->error;
        }
        return call_user_func([$this->authAnnotation -> app -> make($class), $method ?: 'handle'], $this);
    }
}