<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\MessagingServiceProvider;
use App\Providers\DomainEventsServiceProvider;

return [
    AppServiceProvider::class,
    DomainEventsServiceProvider::class,
    FortifyServiceProvider::class,
    MessagingServiceProvider::class,
];
