<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class OutagesController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * The module name.
     *
     * @var string
     */
    protected $moduleName = 'outages';

    /**
     * The view path.
     *
     * @var string
     */
    protected $viewPath = 'outages::';

    /**
     * The route prefix.
     *
     * @var string
     */
    protected $routePrefix = 'outages';

    /**
     * The model name.
     *
     * @var string
     */
    protected $modelName = 'Outage';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('permission:view-outage-menu')->only(['index']);
        $this->middleware('permission:view-create-outage')->only(['create', 'store']);
        $this->middleware('permission:view-view-outage')->only(['show']);
        $this->middleware('permission:view-edit-outage')->only(['edit', 'update']);
        $this->middleware('permission:view-delete-outage')->only(['destroy']);
        $this->middleware('permission:view-dashboard-outage')->only(['dashboard']);
        $this->middleware('permission:view-outage-download-reports')->only(['reports']);
        
        // Set view path
        view()->share('viewPath', $this->viewPath);
        view()->share('routePrefix', $this->routePrefix);
        view()->share('moduleName', $this->moduleName);
    }

    /**
     * Get the view path.
     *
     * @param string $path
     * @return string
     */
    protected function view($path)
    {
        return $this->viewPath . $path;
    }
}
