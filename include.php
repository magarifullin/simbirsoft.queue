<?php
declare(strict_types=1);

use Bitrix\Main\Loader;
Loader::includeModule('main');

spl_autoload_register(static function ($namespace) {
    $nsPaths = [
        'Simbirsoft\\Queue' => '',
    ];
    $basePath = __DIR__ .'/lib/';
    foreach ($nsPaths as $ns => $path) {
        if (0 === strpos($namespace, $ns)) {
            $rest = substr($namespace, strlen($ns));
            $rest = str_replace('\\', '/', $rest);
            $file = $basePath . $path . trim($rest, '/') .'.php';

            if (file_exists($file)) {
                require_once($file);
            }
        }
    }
});
