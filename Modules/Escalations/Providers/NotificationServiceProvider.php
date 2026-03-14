<?php

namespace Modules\Escalations\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Modules\Escalations\Entities\EscalationNotification;
use Illuminate\Support\Facades\Auth;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the notification component
        Blade::component('escalations::components.notification-menu', 'notification-menu');
        
        // Share notification count with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $unreadCount = EscalationNotification::where('user_id', Auth::id())
                    ->where('read', false)
                    ->count();
                
                $view->with('unreadNotificationCount', $unreadCount);
            }
        });
    }
}
