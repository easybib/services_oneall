<?php
/**
 * Simple example to list all the users who have connected to your site.
 *
 * Requires your dependencies (and autoloader) via composer and a `config.php` file in `examples/`.
 *
 * @author Till Klampaeckel <till@php.net>
 */

require dirname(__DIR__) . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$oneAll = new EasyBib\Services\OneAll(
    $config['public'],
    $config['private'],
    $config['subdomain']
);
$users = $oneAll->getUsers();

foreach ($users->entries as $entry) {
    $token = $entry->user_token;
    var_dump($oneAll->getUser($token));
}
