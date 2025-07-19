<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      Mail::alwaysTo('m@marceli.to');
      // if (!app()->environment('production')) {
      //   Mail::alwaysTo('m@marceli.to');
      // }
    }
}
