<?php

namespace App\Http\Controllers;

use App\Classes\BaseController;

use Illuminate\Http\Request;

class AppController extends BaseController
{
    /**
     * Show the index page
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        return view('app.index');
    }

    /**
     * Show the home page when logged in
     *
     * @return \Illuminate\Http\Response
     */
    public function getAccount()
    {
        return view('app.account');
    }
}
