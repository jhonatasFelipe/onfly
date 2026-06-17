<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API autenticada (travel-orders, logout)
    |--------------------------------------------------------------------------
    */
    'api' => [
        'max_attempts' => (int) env('RATE_LIMIT_API', 60),
        'decay_minutes' => 1,
        'identifier' => 'usuário autenticado ou IP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autenticação API (login, register)
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'max_attempts' => (int) env('RATE_LIMIT_AUTH', 10),
        'decay_minutes' => 1,
        'identifier' => 'IP do cliente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotas web gerais
    |--------------------------------------------------------------------------
    */
    'web' => [
        'max_attempts' => (int) env('RATE_LIMIT_WEB', 60),
        'decay_minutes' => 1,
        'identifier' => 'IP do cliente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Login web de administrador
    |--------------------------------------------------------------------------
    */
    'web-login' => [
        'max_attempts' => (int) env('RATE_LIMIT_WEB_LOGIN', 5),
        'decay_minutes' => 1,
        'identifier' => 'IP do cliente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentação OpenAPI (Scramble)
    |--------------------------------------------------------------------------
    */
    'docs' => [
        'max_attempts' => (int) env('RATE_LIMIT_DOCS', 30),
        'decay_minutes' => 1,
        'identifier' => 'usuário autenticado ou IP',
    ],
];
