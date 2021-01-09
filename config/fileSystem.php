<?php
return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'type' => 'local',
            // 根目录
            'rootPath' => app() -> getRootPath(),
            'file' => [
                'public' => 0640,
                'private' => 0604,
            ],
            'dir' => [
                'public' => 0740,
                'private' => 7604,
            ]
        ],
        'aws' => [
            // 磁盘类型
            'type'       => 'awsS3',
            'bucket'     => 'bucket',
            // Optional path prefix
            'prefix'     => '',

            // Visibility converter
            'visibility' => 'public'
        ],
        'ftp' => [
            'type' => 'ftp',
            'host' => 'hostname', // required
            'rootPath' => '/root/path/', // required
            'username' => 'username', // required
            'password' => 'password', // required
            'port' => 21,
            'ssl' => false,
            'timeout' => 90,
            'utf8' => false,
            'passive' => true,
            'transferMode' => FTP_BINARY,
            'systemType' => null, // 'windows' or 'unix'
            'ignorePassiveAddress' => null, // true or false
            'timestampsOnUnixListingsEnabled' => false, // true or false
            'recurseManually' => true // true
        ],
        'sftp' => [
            'type' => 'sftp',
            'options' => [
                'host' => 'hostname',
                'rootPath' => '/root/path/',
                'username' => 'username',
                'password' => 'password',
                'privateKey' => null,
                'passphrase' => null,
                'useAgent' => false,
                'timeout' => 90,
                'maxTries' => 4,
                'hostFingerprint' => 4,
            ],
            'file' => [
                'public' => 0640,
                'private' => 0604,
            ],
            'dir' => [
                'public' => 0740,
                'private' => 7604,
            ]
        ]
    ]
];
