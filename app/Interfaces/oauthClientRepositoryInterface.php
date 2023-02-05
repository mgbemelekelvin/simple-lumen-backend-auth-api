<?php

namespace App\Interfaces;

interface oauthClientRepositoryInterface
{
    public function getClientID($request);
    public function createClientID($request);
}
