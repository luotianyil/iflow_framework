<?php

namespace iflow\http\Hook\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestBeforeHook extends RequestHook {

    protected string $hookName = 'RequestBeforeHook';

}
