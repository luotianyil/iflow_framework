<?php
namespace app;

use iflow\router\lib\Router;

#[Router('/')]
class controller
{
    #[Router('/', 'get')]
    public function home(): bool
    {
        return sendFile('Index.html');
    }

    #[Router('index', 'post')]
    public function index(bean $bean, string $a = '', int $c = 1)
    {
        return message() -> success('Hello World', [
            'bean' => $bean,
            'a' => $a,
            'c' => $c,
        ]);
    }

    #[Router('index/<?:a>/<[0-9]{1}:b>', 'get')]
    public function indexPath(string $a = '', string $c = "")
    {
        return message() -> success('This is Get Methods');
    }

    #[Router('upFile', 'post')]
    public function upFile()
    {
        var_dump(request() -> file('file')[0] -> md5());
        return message() -> success('This is upFile Methods');
    }

    #[Router('socketDemo', 'get')]
    public function socketDemo()
    {
        return sendFile('demo.html');
    }
}