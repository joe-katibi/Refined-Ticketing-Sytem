<?php

namespace App\Providers;

use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\SanctumServiceProvider as BaseSanctumServiceProvider;
use Illuminate\Foundation\Application;

class MobileSanctumServiceProvider extends BaseSanctumServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();
        
        // Configure mobile guard for stateless authentication
        $this->app->config('sanctum.guard', 'mobile');
    }
}
