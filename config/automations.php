<?php

return [
    'timezone' => env('AUTOMATION_TIMEZONE', 'Europe/Budapest'),
    'retention_months' => (int) env('AUTOMATION_RETENTION_MONTHS', 12),
    'dispatch_batch' => (int) env('AUTOMATION_DISPATCH_BATCH', 500),
];
