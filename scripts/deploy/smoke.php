<?php

// Credentials stay in the environment; never print response bodies or credentials.
$base = rtrim(getenv('STAGING_URL') ?: 'https://ebtest.dekor26.hu', '/');
$revision = getenv('RELEASE_SHA') ?: '';
$user = getenv('STAGING_ACCESS_USER') ?: '';
$password = getenv('STAGING_ACCESS_PASSWORD') ?: '';
if ($base !== 'https://ebtest.dekor26.hu' || !preg_match('/^[a-f0-9]{40}$/D', $revision)
    || $user === '' || $password === '') {
    fwrite(STDERR, "Invalid smoke test target, revision or credentials.\n");
    exit(1);
}

foreach ([['/', false, 401], ['/', true, 200], ['/contact', true, 200], ['/login', true, 200], ['/account', true, 302], ['/adminpanel', true, 302]] as [$path, $authenticate, $expected]) {
    $headers = [];
    $handle = curl_init($base.$path);
    curl_setopt_array($handle, [
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_WRITEFUNCTION => static fn ($curl, $body) => strlen($body),
        CURLOPT_HEADERFUNCTION => static function ($curl, $line) use (&$headers) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
            return strlen($line);
        },
    ]);
    if ($authenticate) {
        curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($handle, CURLOPT_USERPWD, $user.':'.$password);
    }
    $success = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    if ($success === false || $status !== $expected
        || !str_contains($headers['x-robots-tag'] ?? '', 'noindex')
        || ($authenticate && ($headers['x-release-sha'] ?? '') !== $revision)) {
        fwrite(STDERR, "Smoke check failed for $path (HTTP $status). Check status, noindex and revision.\n");
        exit(1);
    }
    echo "Verified $path (HTTP $status).\n";
}
