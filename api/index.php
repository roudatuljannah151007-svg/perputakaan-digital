<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Siapkan folder writable di /tmp untuk Vercel
$storagePaths = [
    '/tmp/storage/app',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

$_ENV['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes-v7.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/bootstrap/cache/events.php';

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

// 2. Tangkap request dan paksa header Set-Cookie diteruskan ke Vercel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Request::capture());

foreach ($response->headers->getCookies() as $cookie) {
    setcookie(
        $cookie->getName(),
        $cookie->getValue() ?? '',
        [
            'expires' => $cookie->getExpiresTime(),
            'path' => $cookie->getPath(),
            'domain' => $cookie->getDomain() ?? '',
            'secure' => true,
            'httponly' => $cookie->isHttpOnly(),
            'samesite' => 'Lax',
        ]
    );
}

$response->send();
$kernel->terminate($request, $response);