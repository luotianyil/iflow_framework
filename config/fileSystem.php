<?php
return [
    'default' => 'local',
    'disks' => [
        'local' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        'aws' => [
            // 磁盘类型
            'type'       => 'awsS3',
        ]
    ]
];
