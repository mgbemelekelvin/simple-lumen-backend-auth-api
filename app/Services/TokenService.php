<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TokenService {
    public static function getAccessToken()
    {
        if (Auth::check()){
            return 'Bearer ' . Auth::user()->getToken(Auth::user()->id);
        } else {
            return false;
        }
    }

    public static function getClientID()
    {
        return request()->header('Client-Id');
    }

    public static function getAppName()
    {
        if (Session::has('AppName')){
            return Session::get('AppName');
        } else {
            return false;
        }
    }

    public static function getAppCountry()
    {
        if (Session::has('AppCountry')){
            return Session::get('AppCountry');
        } else {
            return false;
        }
    }

}
