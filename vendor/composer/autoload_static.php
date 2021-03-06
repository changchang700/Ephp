<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0a8e6ef2d30e7d95a5d7a14e48902d61
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Views\\' => 6,
        ),
        'T' => 
        array (
            'Timer\\' => 6,
            'Task\\' => 5,
        ),
        'S' => 
        array (
            'Server\\' => 7,
        ),
        'R' => 
        array (
            'Route\\' => 6,
        ),
        'P' => 
        array (
            'Process\\' => 8,
            'Pack\\' => 5,
        ),
        'M' => 
        array (
            'Models\\' => 7,
            'Marco\\' => 6,
        ),
        'D' => 
        array (
            'Db\\' => 3,
        ),
        'C' => 
        array (
            'Core\\' => 5,
            'Controllers\\' => 12,
            'Console\\' => 8,
            'Config\\' => 7,
            'Components\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Views\\' => 
        array (
            0 => __DIR__ . '/../..' . '/views',
        ),
        'Timer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/timer',
        ),
        'Task\\' => 
        array (
            0 => __DIR__ . '/../..' . '/task',
        ),
        'Server\\' => 
        array (
            0 => __DIR__ . '/../..' . '/server',
        ),
        'Route\\' => 
        array (
            0 => __DIR__ . '/../..' . '/route',
        ),
        'Process\\' => 
        array (
            0 => __DIR__ . '/../..' . '/process',
        ),
        'Pack\\' => 
        array (
            0 => __DIR__ . '/../..' . '/pack',
        ),
        'Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/models',
        ),
        'Marco\\' => 
        array (
            0 => __DIR__ . '/../..' . '/marco',
        ),
        'Db\\' => 
        array (
            0 => __DIR__ . '/../..' . '/db',
        ),
        'Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
        'Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/controllers',
        ),
        'Console\\' => 
        array (
            0 => __DIR__ . '/../..' . '/console',
        ),
        'Config\\' => 
        array (
            0 => __DIR__ . '/../..' . '/config',
        ),
        'Components\\' => 
        array (
            0 => __DIR__ . '/../..' . '/components',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0a8e6ef2d30e7d95a5d7a14e48902d61::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0a8e6ef2d30e7d95a5d7a14e48902d61::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
