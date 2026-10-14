<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;



// Deployment writes the exact application root; local development uses ../.
$pathFile = __DIR__.'/.laravel-path.php';
$appPath = is_file($pathFile) ? require $pathFile : dirname(__DIR__);
$appPath = is_string($appPath) ? realpath($appPath) : false;

if (!$appPath) {
    error_log('Laravel deployment: configured application root is missing or incomplete.');
    http_response_code(500);
    exit('Application deployment is incomplete.');
}

$_ENV['APP_BASE_PATH'] = $appPath;

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

if (!is_file($appPath.'/vendor/autoload.php') || !is_file($appPath.'/bootstrap/app.php')) {
    error_log('Laravel deployment: configured application root is missing or incomplete.');
    http_response_code(500);
    exit('Application deployment is incomplete.');
}

require $appPath.'/vendor/autoload.php';
$app = require_once $appPath.'/bootstrap/app.php';

$app->bind('path.public', function() {
    return __DIR__;
});

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
