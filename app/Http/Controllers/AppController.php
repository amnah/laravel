<?php

namespace App\Http\Controllers;

use App\Classes\BaseController;
use Illuminate\Http\Request;

class AppController extends BaseController
{
    public function getIndex(Request $request)
    {
        return view('app/index');
    }

    public function getAccount()
    {
        return view('app/account');
    }
}
