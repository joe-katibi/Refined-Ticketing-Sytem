<?php

namespace Modules\Escalations\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InjectNotificationComponents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Temporarily disable injection to prevent errors
        return $response;

        // Only inject on HTML responses when user is authenticated
        if (!Auth::check() || !$this->isHtmlResponse($response)) {
            return $response;
        }

        $content = $response->getContent();
        
        // Don't modify if content is not a string or is empty
        if (!is_string($content) || empty($content)) {
            return $response;
        }

        try {
            // Inject notification menu into navbar
            $content = $this->injectNotificationMenu($content);
            
            // Inject notification scripts before closing body tag
            $content = $this->injectNotificationScripts($content);
            
            $response->setContent($content);
        } catch (\Exception $e) {
            // If injection fails, return original response
            \Log::error('Notification injection failed: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Check if the response is HTML
     *
     * @param mixed $response
     * @return bool
     */
    protected function isHtmlResponse($response)
    {
        if (!$response instanceof Response) {
            return false;
        }
        
        $contentType = $response->headers->get('Content-Type');
        
        return $contentType && Str::contains($contentType, 'text/html');
    }
    
    /**
     * Inject notification menu into navbar
     *
     * @param string $content
     * @return string
     */
    protected function injectNotificationMenu($content)
    {
        // Look for common navbar patterns to inject our notification menu
        $patterns = [
            '/<ul[^>]*class="[^"]*navbar-nav[^"]*"[^>]*>/',
            '/<ul[^>]*class="[^"]*nav-right[^"]*"[^>]*>/',
            '/<ul[^>]*class="[^"]*navbar-right[^"]*"[^>]*>/'
        ];
        
        $notificationMenu = view('escalations::components.notification-menu')->render();
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $position = $matches[0][1] + strlen($matches[0][0]);
                $content = substr_replace($content, $notificationMenu, $position, 0);
                break;
            }
        }
        
        return $content;
    }
    
    /**
     * Inject notification scripts before closing body tag
     *
     * @param string $content
     * @return string
     */
    protected function injectNotificationScripts($content)
    {
        $scripts = view('escalations::partials.notification-scripts')->render();
        
        // Insert before closing body tag
        $content = str_replace('</body>', $scripts . '</body>', $content);
        
        return $content;
    }
}
