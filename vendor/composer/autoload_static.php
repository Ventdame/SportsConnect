<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7e56f8b983a81fcb4e437d5e1fdbaab5
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'config\\' => 7,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'config\\' => 
        array (
            0 => __DIR__ . '/../..' . '/config',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7e56f8b983a81fcb4e437d5e1fdbaab5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7e56f8b983a81fcb4e437d5e1fdbaab5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7e56f8b983a81fcb4e437d5e1fdbaab5::$classMap;

        }, null, ClassLoader::class);
    }
}
