<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6f6a29d29d332f9f30acfb3be08485a0
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zend\\Validator\\' => 15,
            'Zend\\Stdlib\\' => 12,
            'Zend\\Escaper\\' => 13,
        ),
        'P' => 
        array (
            'PhpOffice\\PhpWord\\' => 18,
            'PhpOffice\\Common\\' => 17,
        ),
        'D' => 
        array (
            'Dompdf\\' => 7,
        ),
        'B' => 
        array (
            'Box\\Spout\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zend\\Validator\\' => 
        array (
            0 => __DIR__ . '/..' . '/zendframework/zend-validator/src',
        ),
        'Zend\\Stdlib\\' => 
        array (
            0 => __DIR__ . '/..' . '/zendframework/zend-stdlib/src',
        ),
        'Zend\\Escaper\\' => 
        array (
            0 => __DIR__ . '/..' . '/zendframework/zend-escaper/src',
        ),
        'PhpOffice\\PhpWord\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpword/src/PhpWord',
        ),
        'PhpOffice\\Common\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/common/src/Common',
        ),
        'Dompdf\\' => 
        array (
            0 => __DIR__ . '/..' . '/dompdf/dompdf/src',
        ),
        'Box\\Spout\\' => 
        array (
            0 => __DIR__ . '/..' . '/box/spout/src/Spout',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Svg\\' => 
            array (
                0 => __DIR__ . '/..' . '/phenx/php-svg-lib/src',
            ),
        ),
        'F' => 
        array (
            'FontLib\\' => 
            array (
                0 => __DIR__ . '/..' . '/phenx/php-font-lib/src',
            ),
        ),
    );

    public static $classMap = array (
        'Cpdf' => __DIR__ . '/..' . '/dompdf/dompdf/lib/Cpdf.php',
        'HTML5_Data' => __DIR__ . '/..' . '/dompdf/dompdf/lib/html5lib/Data.php',
        'HTML5_InputStream' => __DIR__ . '/..' . '/dompdf/dompdf/lib/html5lib/InputStream.php',
        'HTML5_Parser' => __DIR__ . '/..' . '/dompdf/dompdf/lib/html5lib/Parser.php',
        'HTML5_Tokenizer' => __DIR__ . '/..' . '/dompdf/dompdf/lib/html5lib/Tokenizer.php',
        'HTML5_TreeBuilder' => __DIR__ . '/..' . '/dompdf/dompdf/lib/html5lib/TreeBuilder.php',
        'PclZip' => __DIR__ . '/..' . '/pclzip/pclzip/pclzip.lib.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6f6a29d29d332f9f30acfb3be08485a0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6f6a29d29d332f9f30acfb3be08485a0::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit6f6a29d29d332f9f30acfb3be08485a0::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit6f6a29d29d332f9f30acfb3be08485a0::$classMap;

        }, null, ClassLoader::class);
    }
}