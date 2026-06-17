<?php

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    EventServiceProvider::class,
];
