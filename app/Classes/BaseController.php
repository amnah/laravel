<?php

namespace App\Classes;

use Debugbar;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // check if debugbar should be enabled via $_GET/$_COOKIE param
        if (!Debugbar::enabled() && $this->checkEnableDebugbar()) {
            Debugbar::enable();
        }
    }

    protected function checkEnableDebugbar()
    {
        // check $_GET and $_COOKIE
        // enable by manually entering the url "http://example.com?<password>"
        $cookieName = '_forceDebug';
        $cookieExpire = app()->environment('production') ? 60*15 : 60*60*24; // 15 minutes for production, 1 day for others
        $debugPassword = config('debugbar.password');
        $isGetSet = isset($_GET[$debugPassword]);
        $isCookieSet = (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $debugPassword);

        // set/refresh cookie
        $result = false;
        if ($debugPassword && ($isGetSet || $isCookieSet)) {
            $result = setcookie($cookieName, $debugPassword, time() + $cookieExpire);
        }
        return $result;
    }
}
