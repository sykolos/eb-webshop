<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;



// if (file_exists($maintenance = __DIR__.'/../myapp/storage/framework/maintenance.php')) {
//     require $maintenance;
// }
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';
// require __DIR__.'/../myapp/vendor/autoload.php';

// $app = require_once __DIR__.'/../myapp/bootstrap/app.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->bind('path.public', function() {
    return __DIR__;
});

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

