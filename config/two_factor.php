<?php

return [
    'issuer' => env('TWO_FACTOR_ISSUER', env('APP_NAME', 'ElectroBusiness')),
    'digits' => 6,
    'period' => 30,
    'recovery_code_count' => 8,
    'pending_session_key' => 'admin_2fa_pending',
    'verified_session_key' => 'admin_2fa_verified',
];
