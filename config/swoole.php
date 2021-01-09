<?php
//return [
//    'host' => '127.0.0.1',
//    'port' => 8089,
//    'websocket' => [
//        'enable' => true,
//        'ping_interval' => 25000,
//        'ping_timeout'  => 60000
//    ],
//
//    'udpSwooleConfig' => [
//        'pid_file'              => runtime_path() . 'udp.pid',
//        'log_file'              => runtime_path() . 'udp.log',
//        'daemonize'             => false,
//        'reactor_num'           => swoole_cpu_num(),
//        'worker_num'            => swoole_cpu_num(),
//        'task_worker_num'       => swoole_cpu_num(),
//        'enable_static_handler' => true,
//        'package_max_length'    => 20 * 1024 * 1024,
//        'buffer_output_size'    => 10 * 1024 * 1024,
//        'socket_buffer_size'    => 128 * 1024 * 1024,
//        'serverHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8090
//        ],
//        'clientHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8090,
//            'timeout' => 0.5
//        ],
//        'Handle' => [
//            'ServerHandle' => '',
//            'ClientHandle' => '',
//        ]
//    ],
//
//    'serviceSwooleConfig' => [
//        'pid_file'              => runtime_path() . 'service.pid',
//        'log_file'              => runtime_path() . 'service.log',
//        'daemonize'             => false,
//        'reactor_num'           => swoole_cpu_num(),
//        'worker_num'            => swoole_cpu_num(),
//        'task_worker_num'       => swoole_cpu_num(),
//        'enable_static_handler' => true,
//        'package_max_length'    => 20 * 1024 * 1024,
//        'buffer_output_size'    => 10 * 1024 * 1024,
//        'socket_buffer_size'    => 128 * 1024 * 1024
//    ],
//
//    'tcpSwooleConfig' => [
//        'pid_file'              => runtime_path() . 'tcp.pid',
//        'log_file'              => runtime_path() . 'tcp.log',
//        'daemonize'             => false,
//        'reactor_num'           => swoole_cpu_num(),
//        'worker_num'            => swoole_cpu_num(),
//        'task_worker_num'       => swoole_cpu_num(),
//        'enable_static_handler' => true,
//        'package_max_length'    => 20 * 1024 * 1024,
//        'buffer_output_size'    => 10 * 1024 * 1024,
//        'socket_buffer_size'    => 128 * 1024 * 1024,
//        'serverHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8091
//        ],
//        'clientHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8091
//        ],
//        'Handle' => [
//            'ServerHandle' => '',
//            'ClientHandle' => '',
//        ]
//    ],
//
//    'mqttSwooleConfig' => [
//        'pid_file'              => runtime_path() . 'mqtt.pid',
//        'log_file'              => runtime_path() . 'mqtt.log',
//        'open_mqtt_protocol'    => true,
//        'daemonize'             => false,
//        'reactor_num'           => swoole_cpu_num(),
//        'worker_num'            => swoole_cpu_num(),
//        'task_worker_num'       => swoole_cpu_num(),
//        'enable_static_handler' => true,
//        'package_max_length'    => 20 * 1024 * 1024,
//        'buffer_output_size'    => 10 * 1024 * 1024,
//        'socket_buffer_size'    => 128 * 1024 * 1024,
//
//        // ssl
////        'ssl_cert_file' => '',
////        'ssl_key_file' => '',
////        'ssl_verify_peer' => '',
////        'ssl_allow_self_signed' => '',
////        'ssl_host_name' => '',
////        'ssl_ca_file' => '',
////        'ssl_ca_path' => '',
//        'serverHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8092
//        ],
//        'clientHost' => [
//            'host' => '127.0.0.1',
//            'port' => 8092
//        ],
//        'Handle' => [
//            'ServerHandle' => '',
//            'ClientHandle' => '',
//        ]
//    ]
//];