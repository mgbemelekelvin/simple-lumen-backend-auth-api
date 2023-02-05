<?php

namespace App\Repositories;

use App\Http\Validations\UserValidation;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\sendNotification;
use App\Models\User;
use App\Services\Queries;
use App\Services\UserService;
use App\Traits\Responses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserRepository implements UserRepositoryInterface
{
    use Responses;

    protected $userValidation;

    public function __construct(UserValidation $userValidation)
    {
        $this->userValidation = $userValidation;
    }

    public function registerUser($request)
    {
        try {
            //validate
            $validate = $this->userValidation->registerUser($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            //Check if email exit
            $checkEmail = (object)json_decode(self::emailCheck($request)->content(), true);
            if ($checkEmail->status == 200) return $this->response(409, 'A user with email ('.$request->email.') already exist','');
            //Check if phone number exit
            $checkPhoneNumber = (object)json_decode(self::phoneCheck($request)->content(), true);
            if ($checkPhoneNumber->status == 200) return $this->response(409, 'A user with phone number (+'.ltrim($request->country_prefix, '+').'-'.ltrim($request->phone_number, '0').') already exist','');

            DB::beginTransaction();
            $new = new User();
            $new->first_name = $request->first_name;
            $new->last_name = $request->last_name;
            $new->gender = $request->gender;
            $new->email = $request->email;
            $new->country_prefix = ltrim($request->country_prefix, '+');
            $new->phone_number = ltrim($request->phone_number, '0');
            $new->password = Hash::make($request->password);
            $new->active = true;
            $new->save();
            //Send mail to the user\
            $data = [
                'subject' => "New Registration",
                'name' => $request->first_name.' '.$request->last_name,
                'email_message_1' => '<p>You are have successfully registered yourself. <br> Thanks</p>'
            ];
            Mail::to($request->email)->send(new sendNotification($data));
            DB::commit();
            return $this->response(200,'Registered User Successfully',$new);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Register User', $th);
        }
    }

    public function getUsers($request)
    {
        try {
            $users = User::query();
            $query = $users->orderBy('email');
            if (isset($request['query']['filters'])){
                Queries::queries($query, $request['query']['filters']);
            }
            $users = $query->get();
            foreach ($users as $user){
                $data[] = $user->views(null,null);
            }
            return $this->response(200,'Users retrieved successfully', $data??null);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Get Users', $th);
        }
    }

    public function emailCheck($request)
    {
        try {
            //validate
            $validate = $this->userValidation->emailRequired($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');

            if (isset($request->withTrashed) && $request->withTrashed){
                $user = User::where('email', $request->email)->withTrashed()->first();
            } else {
                $user = User::where('email', $request->email)->first();
            }
            if (!$user) return $this->response(404,'Email not found!', '');
            return $this->response(200,true, $user->only(['id','email']));
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Email Check', $th);
        }
    }

    public function phoneCheck($request)
    {
        try {
            //validate
            $validate = $this->userValidation->phoneRequired($request);
            if (isset($validate->status)) return $this->response(400, $validate->message, '');
            $user = User::query();
            $query= $user->where('country_prefix', ltrim($request->country_prefix, '+'))->where('phone_number', ltrim($request->phone_number, '0'));
            if (isset($request->withTrashed) && $request->withTrashed){
                $query = $query->withTrashed();
            }
            if (isset($request->user_id)){
                $query = $query->where('id', '!=', $request->user_id);
            }
            $user = $query->first();
            if (!$user) return $this->response(404,'Phone Number not found!', '');
            return $this->response(200,true, $user->only(['id','country_prefix','phone_number']));
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->error(['slack'], 'Phone Check', $th);
        }
    }
}
