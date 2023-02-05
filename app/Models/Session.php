<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'token', 'user_id', 'status'
    ];
    public function loginDevices(){
        return $this->hasMany(Login_device::class);
    }

    public function oauth_access_token(){
        return $this->belongsTo(oauth_access_token::class, 'oauth_access_token_id');
    }
}
