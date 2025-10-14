<?php

use App\Core\Application\Container\ApplicationContainer;
use App\Providers\DatabaseServiceProvider;
use App\Providers\ExtraServiceServiceProvider;

return [
    'providers' => [
        ApplicationContainer::class,
        ExtraServiceServiceProvider::class,
        DatabaseServiceProvider::class,
    ],

    'middleware' => [
        //
    ]
]; 