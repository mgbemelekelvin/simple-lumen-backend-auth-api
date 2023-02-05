<?php

namespace App\Http\Middleware;

use App\Models\OauthClient;
use App\Services\TokenService;
use App\Traits\Responses;
use Closure;
use Illuminate\Support\Facades\Session;

class CheckApp
{
    use responses;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!TokenService::getClientID()) return $this->response(400, 'Failed! URL ACCESS NOT FOUND!', '');
        $clientID = TokenService::getClientID();
        $checkClientID = OauthClient::where('secret', $clientID)->where('revoked', false)->first();
        if (!$checkClientID) return $this->response(400, 'Failed! URL ACCESS NOT VALID!', '');
        Session::put('AppName', json_decode($checkClientID->name)->name);
        Session::put('AppCountry', json_decode($checkClientID->name)->country);
        return $next($request);
    }
}
