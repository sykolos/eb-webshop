<?php

return [
    // D14: retain the existing server behavior; additional staging guards are deferred.
    'enabled' => false,
    'username' => env('STAGING_ACCESS_USER', ''),
    'password_hash' => env('STAGING_ACCESS_PASSWORD_HASH', ''),
    'mail_to' => env('STAGING_MAIL_TO', ''),
    'revision' => is_file(base_path('REVISION')) ? trim(file_get_contents(base_path('REVISION'))) : null,
];
