<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'id','email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function views($view, $option)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'email' => $this->email,
            'country_prefix' => $this->country_prefix,
            'phone_number' => $this->phone_number,
            'email_verification' => $this->email_verified_at,
            'phone_verification' => $this->phone_number_verified_at,
            'kyc_confirmation' => $this->kyc_confirmation,
        ];
    }

    public function activeSession()
    {
        return $this->hasMany(Session::class)->limit(1);
    }

    public static function getUser($user_id)
    {
        $userCheck = User::where('id', $user_id)->first();
        if ($userCheck){
            return $userCheck->setAttribute('access_token',self::getToken($userCheck->id));
        } else {
            return null;
        }
    }

    public static function getToken($user_id)
    {
        $session = Session::where('user_id', $user_id)->first();
        if ($session){
            return $session->token;
        }
        return false;
    }
}
