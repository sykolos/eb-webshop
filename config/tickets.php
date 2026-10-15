<?php

return [
    'statuses' => ['new', 'in_progress', 'waiting_customer', 'resolved', 'closed'],
    'status_labels' => [
        'new' => 'Új',
        'in_progress' => 'Folyamatban',
        'waiting_customer' => 'Válaszra vár',
        'resolved' => 'Megoldva',
        'closed' => 'Lezárva',
    ],
    'types' => ['general', 'order', 'product', 'billing', 'technical'],
    'type_labels' => [
        'general' => 'Általános',
        'order' => 'Rendelés',
        'product' => 'Termék',
        'billing' => 'Számlázás',
        'technical' => 'Technikai',
    ],
    'priorities' => ['low', 'normal', 'high', 'critical'],
    'priority_labels' => [
        'low' => 'Alacsony',
        'normal' => 'Normál',
        'high' => 'Magas',
        'critical' => 'Kritikus',
    ],
    'response_hours' => [
        'low' => 48,
        'normal' => 24,
        'high' => 8,
        'critical' => 4,
    ],
    'resolution_hours' => [
        'low' => 120,
        'normal' => 72,
        'high' => 24,
        'critical' => 8,
    ],
    'attachment_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
    'attachment_max_kilobytes' => 5120,
    'retention_months' => 24,
];
