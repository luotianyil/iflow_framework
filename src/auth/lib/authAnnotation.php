<?php


namespace iflow\auth\lib;


use iflow\App;
use iflow\auth\lib\exception\AuthorizationException;
use iflow\Response;

#[\Attribute]
class authAnnotation
{

    public array $config = [];
    protected array $initializers = [
        'setUserInfo',
        'setAuthRoles'
    ];

    public App $app;
    public array $router;

    public function __construct(
        public string $key = '',
        public string $role = 'admin|user',
        public array|string $callBack = []
    ) {}

    public function handle($requestTools)
    {
        $this->app = $requestTools -> services -> app;
        $this->router = $requestTools -> router;
        $this->config = config('auth');

        // 处理回调方法
        $configCallBack = is_string($this -> config['callBack']) ? [
            $this -> config['callBack']
        ] : $this -> config['callBack'];
        $this->callBack = array_merge(is_string($this->callBack) ? [$this->callBack] : $this->callBack, $configCallBack);

        $handle = $this->app -> make($this->config['Handle'], [$this], true);
        foreach ($this->initializers as $key) {
            call_user_func([$handle, $key], $requestTools -> request);
        }
        $response = call_user_func([$handle, 'validateAuth'], $requestTools -> request) -> callback();
        if ($response !== true) {
            throw new AuthorizationException($response ?: 'Unauthorized' );
        }
    }

}