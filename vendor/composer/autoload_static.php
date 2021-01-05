<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit55dcd52be8d3231cc976c65d362e64a8
{
    public static $prefixLengthsPsr4 = array (
        'i' => 
        array (
            'iflow\\' => 6,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
            'Psr\\Container\\' => 14,
        ),
        'L' => 
        array (
            'League\\MimeTypeDetection\\' => 25,
            'League\\Flysystem\\' => 17,
        ),
        'B' => 
        array (
            'BinSoul\\Net\\Mqtt\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'iflow\\' => 
        array (
            0 => __DIR__ . '/../..' . '/framework/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'League\\MimeTypeDetection\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/mime-type-detection/src',
        ),
        'League\\Flysystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/flysystem/src',
        ),
        'BinSoul\\Net\\Mqtt\\' => 
        array (
            0 => __DIR__ . '/..' . '/binsoul/net-mqtt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit55dcd52be8d3231cc976c65d362e64a8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit55dcd52be8d3231cc976c65d362e64a8::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
