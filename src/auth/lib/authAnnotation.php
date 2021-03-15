<?php


namespace iflow\auth\lib;


use iflow\App;

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
        public string $callBack = ''
    ) {}

    public function handle($requestTools)
    {
        $this->app = $requestTools -> services -> app;
        $this->router = $requestTools -> router;
        $this->callBack = $this -> callBack ? $this -> config['callBack'] : $this->callBack;

        $this->config = config('auth');
        $handle = $this->app -> make($this->config['Handle'], [$this], true);
        foreach ($this->initializers as $key) {
            call_user_func([$handle, $key], $requestTools -> request);
        }
        return
            call_user_func([$handle, 'validateAuth'], $requestTools -> request)
                -> callback();
    }

}