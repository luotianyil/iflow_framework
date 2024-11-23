<?php


namespace iflow\annotation\value\validate;


use Attribute;
use Error;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\annotation\tools\data\abstracts\DataAbstract;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use ReflectionException;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class ValidateRule extends DataAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::InitializerNonExecute;

    public function __construct(
        protected string|array $rule = [],
        protected string|array $errMsg = [],
        protected mixed $defaultValue = ""
    ) {}

    /**
     * @param Reflector $reflector
     * @param $args
     * @return object|null
     * @throws ReflectionException
     * @throws ValueException
     * @throws InvokeClassException
     */
    public function process(Reflector $reflector, &$args): ?object {
        // TODO: Implement process() method.
        $name = $reflector -> getName();

        $this->rule = $this->toArray($this->rule, $name);
        $this->errMsg = $this->toArray($this->errMsg, $name);

        $defaultValue = $reflector -> getDefaultValue() ?: $this->defaultValue;
        $object = $this->getObject($args);

        // 获取验证参数
        try {
            $defaultValue = $this -> getValue($reflector, $object, $args['parameters']);
        } catch (Error) {}

        try {
            // 设置验证参数
            $reflector -> setValue($object, $defaultValue);
            $this->defaultValue = $this->toArray($reflector->getValue($object), $name);
        } catch (Error) {
            $this->throw_error($reflector, 403);
        }

        try {
            validator($this->rule, $this->defaultValue, $this->errMsg);
        } catch (\Exception $exception) {
            $this->error = $exception -> getMessage();
            $this->throw_error($reflector, 403);
        }

        return $object;
    }

    protected function toArray($value, $name): array {
        return !is_array($value) ? [ $name => $value ]: $value;
    }
}