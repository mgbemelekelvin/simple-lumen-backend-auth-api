<?php

namespace App\Http\Validations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class oauthClientValidation extends Validator
{
    public function createClientID($request)
    {
        $validator_1 = Validator::make($request->all(), [
            'name' => 'required',
            'country_name' => 'required',
            'redirect_url' => 'required',
        ]);
        if ($validator_1->fails()) {
            return (object)[
                'status' => 400,
                'message' => $validator_1->messages()->all()
            ];
        }
    }

}
