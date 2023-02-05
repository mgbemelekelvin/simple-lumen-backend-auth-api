<?php

namespace App\Interfaces;

interface AuthenticationRepositoryInterface
{
    public function login($request);
    public function checkAuth($request);
    public function logout($request);
    public function forgotPasswordOneTimeToken($request);
    public function verifyOnetimeToken($request);
    public function verifyForgotPassword($request);
    public function resetPassword($request);
}
