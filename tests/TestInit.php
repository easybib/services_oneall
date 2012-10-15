<?php
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (false === file_exists($autoloader)) {
    echo "Please `./composer.phar install`." . PHP_EOL;
    exit(1);
}
require $autoloader;
