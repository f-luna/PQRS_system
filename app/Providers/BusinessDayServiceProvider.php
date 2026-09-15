<?php

namespace App\Providers;

use Cmixin\BusinessDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class BusinessDayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        BusinessDay::enable(Carbon::class, 'co');
    }
}
