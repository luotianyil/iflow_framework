<?php


namespace iflow\auth\Authorize;


use Attribute;
use iflow\App;
use iflow\auth\Exceptions\AuthorizationException;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use Reflector;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class AuthAnnotation extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::InitializerNonExecute;

    public array $config = [];
    protected array $initializers = [
        'setUserInfo',
        'setAuthRoles'
    ];

    /**
     * @var App
     */
    public object $app;
    public array $router;

    public function __construct(
        public string $key = '',
        public string $role = 'admin|user',
        public array|string $callBack = []
    ) {}

    /**
     * @throws InvokeClassException
     */
    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.

        $this->app = app();
        $this->router = router();
        $this->config = config('auth');

        $request = request();

        // 处理回调方法
        $configCallBack = is_string($this -> config['callBack']) ? [
            $this -> config['callBack']
        ] : $this -> config['callBack'];
        $this->callBack = array_merge(is_string($this->callBack) ? [$this->callBack] : $this->callBack, $configCallBack);

        $handle = $this->app -> make($this->config['Handle'], [ $this ], true);
        foreach ($this->initializers as $key) {
            call_user_func([$handle, $key], $request);
        }
        $response = call_user_func([$handle, 'validateAuth'], $request) -> callback();
        if ($response !== true) {
            throw new AuthorizationException($response ?: 'Unauthorized' );
        }

        return null;
    }
}