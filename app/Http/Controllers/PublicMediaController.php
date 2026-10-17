<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicMediaController extends Controller
{
    public function show(string $path): Response
    {
        abort_if($path === '' || str_contains($path, '..') || str_contains($path, '\\') || str_starts_with($path, '/'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        $headers = [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
        ];

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, $headers);
    }
}
