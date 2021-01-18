<?php
return [
    'app' => 'run',
    // 存储路由的配置key
    'router' => 'router',
    // 查看 API 文档 地址
    'api_path' => 'e10adc3949ba59abbe56e057f20f883e',
    '404_error_page' => app() -> getRootPath() . 'public'. DIRECTORY_SEPARATOR . '404.html',
    'resources' => [
        'file' => [
            'rule' => 'static',
            'rootPath' => app() -> getRootPath() . 'public'
        ]
    ]
];