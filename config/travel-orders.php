<?php

declare(strict_types=1);

return [
    'pagination' => [
        'default_per_page' => (int) env('TRAVEL_ORDERS_PER_PAGE', 15),
        'max_per_page' => (int) env('TRAVEL_ORDERS_MAX_PER_PAGE', 100),
    ],
];
