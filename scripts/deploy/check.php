<?php

// CLI-only validation; never print configuration values or database credentials.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$appPath = realpath(dirname(__DIR__, 2));
$publicPath = realpath($argv[1] ?? '');
$check = function (bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR, "ERROR: $message\n");
        exit(1);
    }
};

$check($publicPath !== false, 'PUBLIC_PATH does not exist.');
$check(is_file($publicPath.'/.laravel-path.php'), 'Public application path configuration is missing.');
$configuredPath = require $publicPath.'/.laravel-path.php';
$check(is_string($configuredPath) && realpath($configuredPath) === $appPath,
    'Public index and Artisan point to different application directories.');
$check(is_readable($appPath.'/.env'), 'The deployed application has no readable .env next to artisan.');

echo "Application root: $appPath\nPublic directory (required cPanel Document Root): $publicPath\n";
$_ENV['APP_BASE_PATH'] = $appPath;
require $appPath.'/vendor/autoload.php';
$app = require $appPath.'/bootstrap/app.php';
$app->instance('path.public', $publicPath);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $app->make('encrypter');
} catch (Throwable $error) {
    $check(false, 'APP_KEY is missing or invalid in this application. Use its existing valid key; do not rotate it blindly.');
}

foreach (['storage/framework/views', 'storage/framework/sessions', 'storage/framework/cache', 'storage/logs', 'bootstrap/cache'] as $directory) {
    $check(is_writable($appPath.'/'.$directory), "Directory is not writable for the deployment PHP user: $directory");
}

foreach (['home', 'contact', 'login'] as $name) {
    $check($app['router']->getRoutes()->getByName($name) !== null, "Required route missing: $name");
}

foreach (['pages.home', 'pages.contact', 'pages.login'] as $view) {
    $check($app['view']->exists($view), "Required view missing: $view");
}

echo "Entry point, environment key, routes and views verified (CLI).\n";
