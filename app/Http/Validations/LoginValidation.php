<?php

namespace App\Http\Validations;

use Laravel\Lumen\Routing\Controller as BaseController;
//use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class LoginValidation extends BaseController
{
    public function userAgentRequired($request)
    {
        $rules = [
            'user_agent' => 'required',
        ];
        $this->validate($request, $rules);
    }

    public function emailPasswordRequired($request)
    {
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => ['required'],
            //// 'password' => ['required', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised(3)],
        ];
        $this->validate($request, $rules);
    }

    public function socialAuthLogin($request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'user_agent' => 'required',
        ];
        $this->validate($request, $rules);
    }

    public function forgotPasswordOneTimeToken($request)
    {
        $rules = [
            'verificationType' => 'required',
            'email' => 'required|email',
        ];
        $this->validate($request, $rules);
    }

    public function verifyOnetimeToken($request)
    {
        $rules = [
            'code' => 'required',
            'email' => 'required|email',
        ];
        $this->validate($request, $rules);
    }

    public function verifyForgotPassword($request)
    {
        $rules = [
            'code' => 'required',
        ];
        $this->validate($request, $rules);
    }

    public function resetPassword($request)
    {
        $rules = [
            'email' => 'required|email',
            'new_password' => ['required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised(3)],
        ];
        $this->validate($request, $rules);
    }

    public function changePassword($request)
    {
        $rules = [
            'old_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised(3)],
        ];
        $this->validate($request, $rules);
    }

}
