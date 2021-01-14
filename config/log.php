<?php
return [
    'default' => 'file',

    'errorLevelSendEmail' => [
        'error'
    ],
    'time_format' => 'c',

    'file' => [
        'logPath' => app() -> getRuntimePath() . 'logs',
        'format' => '[%s] [%s] %s',
        'json' => false,
        'json_options' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ],
];
