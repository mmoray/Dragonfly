<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9c8a82d27e7232b7aa71971bc03f5985
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Transistor\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Transistor\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Transistor\\Objects\\Breadboard' => __DIR__ . '/../..' . '/src/Objects/Breadboard.php',
        'Transistor\\Objects\\Transistor' => __DIR__ . '/../..' . '/src/Objects/Transistor.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9c8a82d27e7232b7aa71971bc03f5985::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9c8a82d27e7232b7aa71971bc03f5985::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9c8a82d27e7232b7aa71971bc03f5985::$classMap;

        }, null, ClassLoader::class);
    }
}
