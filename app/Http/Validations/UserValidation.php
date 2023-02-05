<?php

namespace App\Http\Validations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserValidation extends Validator
{
    public function registerUser($request)
    {
        $validator_1 = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required|in:Male,Female',
            'country_prefix' => 'required',
            'phone_number' => 'required|numeric',
            'user_agent' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised(3)],
        ]);
        if ($validator_1->fails()) {
            return (object)[
                'status' => 400,
                'message' => $validator_1->messages()->all()
            ];
        }
    }

    public function updateUser($request)
    {
        $validator_1 = Validator::make($request->all(), [
            'country_prefix' => 'required',
            'phone_number' => 'required',
        ]);
        if ($validator_1->fails()) {
            return (object)[
                'status' => 400,
                'message' => $validator_1->messages()->all()
            ];
        }
    }

    public function emailRequired($request)
    {
        $validator_1 = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator_1->fails()) {
            return (object)[
                'status' => 400,
                'message' => $validator_1->messages()->all()
            ];
        }
    }

    public function phoneRequired($request)
    {
        $validator_1 = Validator::make($request->all(), [
            'country_prefix' => 'required',
            'phone_number' => 'required|numeric',
        ]);
        if ($validator_1->fails()) {
            return (object)[
                'status' => 400,
                'message' => $validator_1->messages()->all()
            ];
        }
    }

}
