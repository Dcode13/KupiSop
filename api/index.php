<?php

$runtimeDefaults = [
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'LOG_CHANNEL' => 'stderr',
    'VIEW_COMPILED_PATH' => '/tmp/views',
];

if (! getenv('MYSQL_ATTR_SSL_CA') && is_file('/etc/ssl/certs/ca-certificates.crt')) {
    $runtimeDefaults['MYSQL_ATTR_SSL_CA'] = '/etc/ssl/certs/ca-certificates.crt';
}

foreach ($runtimeDefaults as $key => $value) {
    if (! getenv($key)) {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

foreach (['/tmp/views', '/tmp/framework/cache/data', '/tmp/framework/sessions'] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

require __DIR__.'/../public/index.php';
