<?php

namespace iflow\http\Hook\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestInitializeHook extends RequestHook {

    protected string $hookName = 'RequestInitializeHook';

}