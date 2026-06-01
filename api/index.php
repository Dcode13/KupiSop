<?php

$forcedRuntime = [
    'LARAVEL_STORAGE_PATH' => '/tmp/storage',
    'LOG_CHANNEL' => 'stderr',
    'LOG_STACK' => 'stderr',
];

$runtimeDefaults = [
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'LOG_DEPRECATIONS_CHANNEL' => 'null',
    'VIEW_COMPILED_PATH' => '/tmp/views',
];

if (! getenv('MYSQL_ATTR_SSL_CA') && is_file('/etc/ssl/certs/ca-certificates.crt')) {
    $runtimeDefaults['MYSQL_ATTR_SSL_CA'] = '/etc/ssl/certs/ca-certificates.crt';
}

foreach ($forcedRuntime as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

foreach ($runtimeDefaults as $key => $value) {
    if (getenv($key)) {
        continue;
    }

    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

foreach ([
    '/tmp/storage/app/private',
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/views',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

require __DIR__.'/../public/index.php';
