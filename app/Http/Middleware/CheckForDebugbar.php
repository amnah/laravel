<?php

namespace App\Http\Middleware;

use Closure;
use Barryvdh\Debugbar\LaravelDebugbar;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class CheckForDebugbar
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check if we don't have debugbar
        if (!$this->app->has('debugbar')) {
            return $next($request);
        }

        // check if we need to force enable the debugbar
        /** @var LaravelDebugbar $debugbar */
        $debugbar = $this->app->get('debugbar');
        if (!$debugbar->isEnabled() && $this->checkDebugbarPassword()) {
            $debugbar->enable();
        }

        return $next($request);
    }

    /**
     * Check debugbar password
     *   yes, this is written in basic php instead of laravel format..
     *   this is so I can copy/paste it into other projects easily
     * @return bool
     */
    protected function checkDebugbarPassword()
    {
        // check $_GET and $_COOKIE
        // enable by manually entering the url "http://example.com?<password>"
        $cookieName = '_forceDebug';
        $cookieExpire = 1800; // 1800 = 30 minutes
        $debugPassword = config('debugbar.password');
        $isGetSet = isset($_GET[$debugPassword]);
        $isCookieSet = (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $debugPassword);

        // set/refresh cookie
        $result = false;
        if ($debugPassword && ($isGetSet || $isCookieSet)) {
            $result = setcookie($cookieName, $debugPassword, time() + $cookieExpire, '/');
        }
        return $result;
    }
}
