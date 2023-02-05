<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function registerUser($request);
    public function getUsers($request);
    public function emailCheck($request);
    public function phoneCheck($request);
}
