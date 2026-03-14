<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Get menu data from json file
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);
        $horizontalMenuData = json_decode($horizontalMenuJson);

        // Share menu data with all views using view composer
        View::composer('*', function ($view) use ($verticalMenuData, $horizontalMenuData) {
            $userPermissions = [];

            // Check auth inside the view composer
            if (Auth::check()) {
                $user = Auth::user();
                $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            }
           // dd(  $userPermissions);

            $view->with('menuData', [
                'menu' => $verticalMenuData->menu,
                'horizontalMenu' => $horizontalMenuData->menu,
                'userPermissions' => $userPermissions,
            ]);
        });
    }
}
