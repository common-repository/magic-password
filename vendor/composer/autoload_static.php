<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc7b4056af572762248912388cd0d5fbe
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TwoFAS\\ValidationRules\\' => 23,
            'TwoFAS\\MagicPassword\\' => 21,
            'TwoFAS\\Encryption\\' => 18,
            'TwoFAS\\Core\\' => 12,
            'TwoFAS\\Account\\' => 15,
            'TwoFAS\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Container\\' => 14,
        ),
        'E' => 
        array (
            'Endroid\\QrCode\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TwoFAS\\ValidationRules\\' => 
        array (
            0 => __DIR__ . '/..' . '/twofas/validation-rules/src',
        ),
        'TwoFAS\\MagicPassword\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'TwoFAS\\Encryption\\' => 
        array (
            0 => __DIR__ . '/..' . '/twofas/encryption/src',
            1 => __DIR__ . '/..' . '/twofas/encryption/tests',
        ),
        'TwoFAS\\Core\\' => 
        array (
            0 => __DIR__ . '/..' . '/twofas/wp-plugin-core/src',
        ),
        'TwoFAS\\Account\\' => 
        array (
            0 => __DIR__ . '/..' . '/twofas/account-sdk/TwoFAS/Account',
        ),
        'TwoFAS\\' => 
        array (
            0 => __DIR__ . '/..' . '/twofas/sdk/TwoFAS',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Endroid\\QrCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/endroid/qr-code/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Twig_' => 
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
        'R' => 
        array (
            'Raven_' => 
            array (
                0 => __DIR__ . '/..' . '/sentry/sentry/lib',
            ),
        ),
        'P' => 
        array (
            'Pimple' => 
            array (
                0 => __DIR__ . '/..' . '/pimple/pimple/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc7b4056af572762248912388cd0d5fbe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc7b4056af572762248912388cd0d5fbe::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitc7b4056af572762248912388cd0d5fbe::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
