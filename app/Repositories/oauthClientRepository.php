<?php

namespace App\Repositories;

use App\Http\Validations\oauthClientValidation;
use App\Interfaces\oauthClientRepositoryInterface;
use App\Models\OauthClient;
use App\Services\Queries;
use App\Traits\Responses;
use Illuminate\Support\Facades\DB;
use Throwable;

class oauthClientRepository implements oauthClientRepositoryInterface
{
    use Responses;

    protected $oauthValidation;

    public function __construct(oauthClientValidation $oauthValidation)
    {
        $this->oauthValidation = $oauthValidation;
    }

    public function getClientID($request)
    {
        try {
            $clientID = OauthClient::query();
            $query = $clientID->where('revoked', false);
            if (!empty($request->clientID)) {
                $query = $query->where('secret', $request->clientID);
            }
            if (isset($request['query']['filters'])){
                Queries::queries($query, $request['query']['filters']);
            }
            $clientID = $query->first();
            return $this->response(200,'',$clientID->views(null,null));
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Get Client ID', $th);
        }
    }

    public function createClientID($request)
    {
        try {
            //validate
            $validate = $this->oauthValidation->createClientID($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            $check = OauthClient::where('name', $request->name)->first();
            if($check) return $this->response(409, 'Failed! Client name already exists.','');
            $new = new OauthClient();
            $new->user_id = $request->user_id;
            $new->name = '{"name":"'.$request->name.'","country":"'.$request->country_name.'"}';
            $new->secret = Str::random(65);
            $new->provider = 'users';
            $new->redirect = $request->redirect_url;
            $new->personal_access_client = 0;
            $new->password_client = 0;
            $new->revoked = false;
            $new->save();
            return $this->response(200,'Oauth Client Created Successfully',$new);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Create Client ID', $th);
        }
    }
}
