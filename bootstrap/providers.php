<?php

use App\Providers\AppServiceProvider;
use App\Providers\BusinessDayServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    BusinessDayServiceProvider::class,
    AdminPanelProvider::class,
];
