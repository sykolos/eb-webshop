<?php

return [
    'rate_limit_per_minute' => (int) env('EMAIL_RATE_LIMIT_PER_MINUTE', 60),
    'retention_months' => (int) env('EMAIL_DELIVERY_RETENTION_MONTHS', 12),
    'timezone' => env('EMAIL_DEFAULT_TIMEZONE', 'Europe/Budapest'),
    'staging_provider_note' => 'A staging környezetben a meglévő STAGING_MAIL_TO sink védi a címzetteket; valódi provider/domain még nincs kiválasztva.',
];
