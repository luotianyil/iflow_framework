<?php
return [
    'server' => [
        'pid_file'              => runtime_path() . 'tcp-server.pid',
        'log_file'              => runtime_path() . 'tcp-server.log',
        'daemonize'             => false,
        'reactor_num'           => swoole_cpu_num(),
        'worker_num'            => swoole_cpu_num(),
        'task_worker_num'       => swoole_cpu_num(),
        'package_max_length'    => 20 * 1024 * 1024,
        'buffer_output_size'    => 10 * 1024 * 1024,
        'socket_buffer_size'    => 128 * 1024 * 1024,
        'host' => [
            'host' => '127.0.0.1',
            'port' => 8091
        ],
        'Handle' => ''
    ],
    'client' => [
        'pid_file'              => runtime_path() . 'tcp-client.pid',
        'log_file'              => runtime_path() . 'tcp-client.log',
        'daemonize'             => false,
        'reactor_num'           => swoole_cpu_num(),
        'worker_num'            => swoole_cpu_num(),
        'task_worker_num'       => swoole_cpu_num(),
        'enable_static_handler' => true,
        'package_max_length'    => 20 * 1024 * 1024,
        'buffer_output_size'    => 10 * 1024 * 1024,
        'socket_buffer_size'    => 128 * 1024 * 1024,
        'host' => [
            'host' => '127.0.0.1',
            'port' => 8091,
            'timeout' => 0.5
        ],
        'Handle' => ''
    ]
];