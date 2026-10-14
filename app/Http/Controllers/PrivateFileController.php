<?php

namespace App\Http\Controllers;

use App\Services\SecureFileStorage;

class PrivateFileController extends Controller
{
    public function download(string $path, SecureFileStorage $storage)
    {
        return $storage->download($path);
    }
}
