<?php
return [
    'pid_file'              => runtime_path() . 'service.pid',
    'log_file'              => runtime_path() . 'service.log',
    'daemonize'             => false,
    'reactor_num'           => swoole_cpu_num(),
    'worker_num'            => swoole_cpu_num(),
    'task_worker_num'       => swoole_cpu_num(),
    'package_max_length'    => 20 * 1024 * 1024,
    'buffer_output_size'    => 10 * 1024 * 1024,
    'socket_buffer_size'    => 128 * 1024 * 1024,
    'websocket' => [
        'enable' => true,
        'ping_interval' => 25000,
        'ping_timeout'  => 60000,
    ],
    // default event
    'Handle' => '',
    'host' => [
        'host' => '127.0.0.1',
        'port' => 8089,
    ]
];