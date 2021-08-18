<?php


namespace iflow\request;

use iflow\request\lib\{helper, validRequest};
use Psr\Http\Message\RequestInterface;

trait RequestTrait
{
    use validRequest, helper;

    protected string $version = "1.1";
    protected ?RequestInterface $requestPsr7 = null;
}