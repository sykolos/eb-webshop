<?php

// CLI-only deployment helper. It never prints the application key.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$envFile = $argv[1] ?? '.env';
if (!is_file($envFile) || !is_readable($envFile) || !is_writable($envFile)) {
    fwrite(STDERR, "ERROR: Cannot read and write the deployment .env file.\n");
    exit(1);
}

$contents = file_get_contents($envFile);
if ($contents === false) {
    fwrite(STDERR, "ERROR: Cannot read the deployment .env file.\n");
    exit(1);
}

$lines = preg_split('/\R/', $contents);
$keyLine = null;
$currentKey = '';

foreach ($lines as $index => $line) {
    if (preg_match('/^\s*APP_KEY\s*=(.*)$/', $line, $matches)) {
        $keyLine = $index;
        $currentKey = trim($matches[1]);
        $currentKey = trim($currentKey, "\"'");
        break;
    }
}

$isValidKey = static function (string $key): bool {
    if ($key === '' || stripos($key, 'CHANGE_ME') !== false) {
        return false;
    }

    if (str_starts_with($key, 'base64:')) {
        $decoded = base64_decode(substr($key, 7), true);
        return $decoded !== false && strlen($decoded) === 32;
    }

    return strlen($key) === 32;
};

if ($isValidKey($currentKey)) {
    echo "APP_KEY is present and valid.\n";
    exit(0);
}

$newLine = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
if ($keyLine === null) {
    $lines[] = $newLine;
} else {
    $lines[$keyLine] = $newLine;
}

$newContents = implode(PHP_EOL, $lines);
if (!str_ends_with($newContents, PHP_EOL)) {
    $newContents .= PHP_EOL;
}

if (file_put_contents($envFile, $newContents) === false) {
    fwrite(STDERR, "ERROR: Cannot update the deployment APP_KEY.\n");
    exit(1);
}

chmod($envFile, 0600);
echo "Generated a missing or placeholder APP_KEY for this deployment environment.\n";
