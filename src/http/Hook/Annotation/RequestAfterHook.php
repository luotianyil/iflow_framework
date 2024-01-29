<?php

namespace iflow\http\Hook\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestAfterHook extends RequestHook {

    protected string $hookName = 'RequestAfterHook';

}
