<?php

namespace App\Http\Controllers;

use App\Interfaces\AuthenticationRepositoryInterface;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Traits\responses;

class AuthenticationController extends BaseController
{
    use responses;
    protected $authenticationRepository;
    public function __construct(AuthenticationRepositoryInterface $authenticationRepository)
    {
        $this->authenticationRepository = $authenticationRepository;
    }

    public function store(Request $request)
    {
        //Login
        return $this->authenticationRepository->login($request);
    }

    public function checkAuth(Request $request)
    {
        //Check Auth
        return $this->authenticationRepository->checkAuth($request);
    }

    public function logout(Request $request)
    {
        //Logout
        return $this->authenticationRepository->logout($request);
    }

    public function forgotPasswordOneTimeToken(Request $request)
    {
        //Generate a token for password forgot request or one time login
        return $this->authenticationRepository->forgotPasswordOneTimeToken($request);
    }

    public function verifyOnetimeToken(Request $request)
    {
        //Verify the One-Time Token
        return $this->authenticationRepository->verifyOnetimeToken($request);
    }

    public function verifyForgotPassword(Request $request)
    {
        //Verify the Forgot Password
        return $this->authenticationRepository->verifyForgotPassword($request);
    }

    public function resetPassword(Request $request)
    {
        //Verify the Forgot Password
        return $this->authenticationRepository->resetPassword($request);
    }

}
