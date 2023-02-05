<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class UserService
{
    public static function user()
    {
        //Getting user details from session
        if (Session::has('user')){
            return Session::get('user');
        } else {
            return false;
        }
    }
}
