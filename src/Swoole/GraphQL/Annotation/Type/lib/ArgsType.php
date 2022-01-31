<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Swoole\GraphQL\Annotation\Type\lib\utils\Types;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArgsType extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::NonExecute;

    public function __construct(protected array $args = []) {}

    public function process(Reflector $reflector, &$args): array {
        // TODO: Implement process() method.
        foreach ($this->args as $argKey => $value) {
            $this->args[$argKey]['type'] = (new Types()) -> getType($this->args[$argKey]['type'] ?? 'string');
        }
        return [ $this -> getArgs() ];
    }


    /**
     * @return array
     */
    public function getArgs(): array {
        return $this->args;
    }
}