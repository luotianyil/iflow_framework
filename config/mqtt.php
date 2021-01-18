<?php

return [
    'server' => [
        'pid_file'              => runtime_path() . 'mqtt-server.pid',
        'log_file'              => runtime_path() . 'mqtt-server.log',
        'open_mqtt_protocol'    => true,
        'daemonize'             => false,
        'reactor_num'           => swoole_cpu_num(),
        'worker_num'            => swoole_cpu_num(),
        'task_worker_num'       => swoole_cpu_num(),
        'enable_static_handler' => true,
        'Handle' => '',
        'mqttEvent' => [
            'connectAfter' => '',
            'protocol_level' => 5, // 协议等级，MQTT3.1.1版本为4，5.0版本为5，MQIsdp为3
        ],
        'options' => [],
        'host' => [
            'host' => '127.0.0.1',
            'port' => 8092
        ]
    ],
    'client' => [
        'host' => '127.0.0.1', // MQTT服务端IP
        'port' => 8092, // MQTT服务端端口
        'user_name' => 'admin', // 用户名
        'password' => '123456', // 密码
        'client_id' => \Simps\MQTT\Client::genClientID(), // 客户端id
        'keep_alive' => 10, // 默认0秒，设置成0代表禁用
        'protocol_name' => 'MQTT', // 协议名，默认为MQTT(3.1.1版本)，也可为MQIsdp(3.1版本)
        'protocol_level' => 5, // 协议等级，MQTT3.1.1版本为4，5.0版本为5，MQIsdp为3
        'properties' => [], // MQTT5 中所需要的属性
        'reconnect_delay' => 3, // 重连时的延迟时间
        'swConfig' => [
            'pid_file'              => runtime_path() . 'mqtt-client.pid',
            'log_file'              => runtime_path() . 'mqtt-client.log',
            'open_mqtt_protocol'    => true,
            'daemonize'             => false,
            'reactor_num'           => swoole_cpu_num(),
            'worker_num'            => swoole_cpu_num(),
            'task_worker_num'       => swoole_cpu_num(),
            'package_max_length'    => 2 * 1024 * 1024
        ], // swoole Config
        'sockType' => SWOOLE_TCP, // | SWOOLE_SSL,
        'clientType' => \Simps\MQTT\Client::SYNC_CLIENT_TYPE,
        'Handle' => '',
        'connectBefore' => ''
    ]
];
