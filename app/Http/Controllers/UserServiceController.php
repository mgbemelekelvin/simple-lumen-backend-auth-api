<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use App\Traits\responses;
use Illuminate\Http\Request;

class UserServiceController extends Controller
{
    use responses;
    protected $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        return $this->userRepository->getUsers($request);
    }

    public function show(Request $request, $any)
    {
        return $any;
    }

    public function store(Request $request)
    {
        return $this->userRepository->registerUser($request);
    }
}
