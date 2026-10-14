<?php

// Keep the established public checks; never log response bodies or cookies.
$base = 'https://ebtest.dekor26.hu';
foreach (['/', '/contact', '/login'] as $path) {
    $handle = curl_init($base.$path);
    curl_setopt_array($handle, [
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_WRITEFUNCTION => static fn ($curl, $body) => strlen($body),
    ]);
    $success = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    if ($success === false || $status !== 200) {
        fwrite(STDERR, "Smoke check failed for $path (HTTP $status). Inspect the server logs.\n");
        exit(1);
    }
    echo "Verified $path (HTTP $status).\n";
}
