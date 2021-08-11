<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;


#[\Attribute]
class resolveType
{

    public function __construct(protected string|\Closure $resolve)
    {}

    public function handle(\ReflectionProperty $reflectionProperty, $object): array {
        $resolve = explode('@', $this->resolve);
        if (count($resolve) > 1) {
            if (!class_exists($resolve[0])) throw new \Exception('GraphQL Resolve does Exists');
            $this -> resolve = match ($resolve[0]) {
                // 当为本类方法
                $object::class => fn() => call_user_func([$object, $resolve[1]], ...[...func_get_args(), $object]),
                // 当为其他类方法
                default => function () use ($resolve) {
                    $object = new $resolve[0];
                    call_user_func([$object, $resolve[1]], ...[...func_get_args(), $object]);
                }
            };
        } else {
            // 匿名方法
            $this->resolve = fn() => call_user_func($resolve[0], ...func_get_args());
        }

        return [
            $this->resolve
        ];
    }

}