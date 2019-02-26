<?php

namespace App\Providers;

use Barryvdh\Debugbar\ServiceProvider;

class DebugbarServiceProvider extends ServiceProvider
{
    /**
     * Enabled flag
     * @var bool
     */
    protected $enabled;

    /**
     * @inheritdoc
     */
    public function register()
    {
        // compute if enabled
        $debug = $this->app->get('config')->get('app.debug');
        $debugbarEnabled = value($this->app->get('config')->get('debugbar.enabled'));
        $enabled = $debugbarEnabled === true || $debugbarEnabled === null && $debug === true;
        $enabled = $enabled && !$this->app->runningInConsole() && !$this->app->environment('testing');
        if (!$enabled) {
           $enabled = $this->checkDebugbarPassword();
        }

        // store result and call parent
        $this->enabled = $enabled;
        if ($enabled) {
            parent::register();
        }
    }

    /**
     * Check debugbar password
     *   yes, this is written in basic php instead of laravel format
     *   this is so I can copy/paste it into other projects easily
     * @return bool
     */
    protected function checkDebugbarPassword()
    {
        // check $_GET and $_COOKIE
        // enable by manually entering the url "http://example.com?<password>"
        $cookieName = '_forceDebug';
        $cookieExpire = 1800; // 1800 = 30 minutes
        $debugPassword = $this->app->get('config')->get('debugbar.password');
        $isGetSet = isset($_GET[$debugPassword]);
        $isCookieSet = (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $debugPassword);

        // set/refresh cookie
        $result = false;
        if ($debugPassword && ($isGetSet || $isCookieSet)) {
            $result = setcookie($cookieName, $debugPassword, time() + $cookieExpire, '/');
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function boot()
    {
        if ($this->enabled) {
            parent::boot();
        }
    }
}
