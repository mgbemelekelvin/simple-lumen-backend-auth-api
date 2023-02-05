<?php
namespace App\Utils;

use App\Models\Session as UserSession;
use App\Models\Login_device;
use App\Repositories\oauthClientRepository;
use App\Services\TokenService;
use App\Traits\Responses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Authentication {

    use Responses;

    protected $oauthRepository;

    public function __construct(oauthClientRepository $oauthRepository)
    {
        $this->oauthRepository = $oauthRepository;
    }

    // Check if user is active
    public function authorizeUser($user){
        $res = ['authorize'=>true,'message' => 'User can login'];
        //check if the user suspended
        if (!$user->active) $res = ['authorize'=>false,'message' => 'You have being suspended from using this application. Please contact your Admin'];
        //check if the user deleted/terminated
        if ($user->trashed()) $res = ['authorize'=>false,'message' => 'You have being terminated from using this application. Please contact your Admin'];
        return $res;
    }

    //Create the Session and Login Device
    public function createSession($user_id, $token, $user_agent){
        // Create new session and login device at once
        DB::transaction(function () use ($user_id, $token, $user_agent) {
            $newSession = $this->storeSession($user_id, $token);
            $this->storeDevice($newSession->id, $user_agent);
        });
    }

    // Create a new session for user
    public function storeSession($user_id, $token){
        $checkSession = UserSession::where('user_id', $user_id)->where('token', $token->accessToken)->first();
        if ($checkSession){
            $session = $checkSession;
        } else {
            $session = new UserSession();
            $session->user_id = $user_id;
            $session->token = $token->accessToken;
            $session->oauth_access_token_id = $token->token['id'];
            $session->save();
        }
        return $session;
    }

    // Create new device for a session
    public function storeDevice($session_id, $user_agent){
        $device = new Login_device();
        $device->session_id = $session_id;
        $device->ip = \request()->ip();
        $device->user_agent = $user_agent;
        $device->save();
    }

}

