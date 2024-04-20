<?php

namespace iflow\http\Annotation;

use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION)]
class Header extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::InitializerNonExecute;

    public function __construct(protected string|array $key, protected string|int $value = '') {
    }

    public function process(Reflector $reflector, &$args): bool {
        // TODO: Implement process() method.
        $headers = is_string($this->key) ? [ $this->key => $this->value ] : $this->key;
        $headersLowers = array_change_key_case($headers);

        if (array_key_exists('status', $headersLowers)) {
            response() -> withStatus(intval($headersLowers['status']));
        }

        response() -> headers($headers);
        return true;
    }
}