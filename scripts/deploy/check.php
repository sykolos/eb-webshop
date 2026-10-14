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

if (in_array('--staging', $argv, true)) {
    $check($app->environment('staging') && config('staging.enabled'), 'Deployment requires APP_ENV=staging.');
    $check(config('app.debug') === false, 'Staging APP_DEBUG must be false.');
    $check(config('app.url') === 'https://ebtest.dekor26.hu', 'Unexpected staging APP_URL.');
    $check(config('staging.username') !== '' && password_get_info((string) config('staging.password_hash'))['algoName'] !== 'unknown',
        'Configure staging access username and a valid password hash.');
    $check((bool) filter_var(config('staging.mail_to'), FILTER_VALIDATE_EMAIL), 'Configure a dedicated STAGING_MAIL_TO sink.');
    $check(config('session.cookie') === 'electrobusiness_staging_session'
        && config('session.domain') === null && config('session.secure') === true,
        'Staging requires a host-only secure session cookie named electrobusiness_staging_session.');
    $check(config('cache.default') === 'file' && config('session.driver') === 'file',
        'M01 staging requires isolated file cache and sessions; shared Redis needs separate review.');
    $check(config('queue.default') === 'sync', 'M01 staging uses sync; isolated workers are configured in M04.');
    $check((bool) preg_match('/^[a-f0-9]{40}$/D', (string) config('staging.revision')), 'Missing build revision.');
}

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
