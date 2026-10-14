<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecureFileStorage
{
    public function storePrivate(UploadedFile $file, string $directory, array $extensions, int $maxKilobytes = 5120): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $extensions = array_map('strtolower', $extensions);
        $mimeByExtension = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'gif' => ['image/gif'],
        ];
        $mime = $file->getMimeType();
        $mimeAllowed = !isset($mimeByExtension[$extension])
            || in_array($mime, $mimeByExtension[$extension], true);

        if (!in_array($extension, $extensions, true)
            || !$file->isValid()
            || !$mimeAllowed
            || $file->getSize() > $maxKilobytes * 1024) {
            throw ValidationException::withMessages([
                'file' => 'A fájl típusa vagy mérete nem engedélyezett.',
            ]);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        if (!Storage::disk('private')->putFileAs('', $file, $path)) {
            throw new \RuntimeException('A privát fájl mentése nem sikerült.');
        }

        return $path;
    }

    public function deletePrivate(?string $path): void
    {
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }

    public function temporaryDownloadUrl(string $path, int $minutes = 10): string
    {
        return URL::temporarySignedRoute(
            'private-file.download',
            now()->addMinutes($minutes),
            ['path' => ltrim($path, '/')],
        );
    }

    public function download(string $path)
    {
        $path = ltrim($path, '/');
        abort_if($path === '' || str_contains($path, '..'), 404);
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path);
    }
}
