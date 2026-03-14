<?php

namespace Modules\Appointment\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Modules\\Appointment\\Http\\Controllers';
    
    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }
    
    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        // Debug: Log the module path and route file existence
        $modulePath = module_path('Appointment');
        $routeFile = $modulePath . '/routes/web.php';
        
        \Log::info('Appointment Module - Module Path: ' . $modulePath);
        \Log::info('Appointment Module - Route File: ' . $routeFile);
        \Log::info('Appointment Module - Route File Exists: ' . (file_exists($routeFile) ? 'Yes' : 'No'));
        
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(function() use ($routeFile) {
                if (file_exists($routeFile)) {
                    require $routeFile;
                    \Log::info('Appointment Module - Routes loaded successfully');
                } else {
                    \Log::error('Appointment Module - Route file not found: ' . $routeFile);
                }
            });
    }
    
    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(module_path('Appointment', '/Routes/api.php'));
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('Modules/Appointment/Routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('Modules/Appointment/Routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
