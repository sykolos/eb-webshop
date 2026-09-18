<?php

// Run on the build runner before packaging public/.
$appPath = rtrim(getenv('DEPLOY_PATH') ?: '', '/');
$publicPath = rtrim(getenv('PUBLIC_PATH') ?: '', '/');

foreach (['DEPLOY_PATH' => $appPath, 'PUBLIC_PATH' => $publicPath] as $name => $path) {
    if (!preg_match('~^/(?:[a-zA-Z0-9_.-]+/)*[a-zA-Z0-9_.-]+$~D', $path)
        || in_array('..', explode('/', $path), true)
        || in_array('.', explode('/', $path), true)) {
        fwrite(STDERR, "$name must be an absolute server directory without spaces or parent traversal.\n");
        exit(1);
    }
}

if ($appPath === $publicPath || str_starts_with($appPath.'/', $publicPath.'/')) {
    fwrite(STDERR, "DEPLOY_PATH must be outside PUBLIC_PATH, and the paths must differ.\n");
    exit(1);
}

$destination = dirname(__DIR__, 2).'/public/.laravel-path.php';
if (file_put_contents($destination, "<?php\nreturn ".var_export($appPath, true).";\n") === false) {
    fwrite(STDERR, "Cannot write the public application path configuration.\n");
    exit(1);
}

echo "Public entry point configured for DEPLOY_PATH.\n";
