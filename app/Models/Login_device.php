<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Login_device extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'ip', 'user_agent', 'session_id', 'status'
    ];
    public function session(){
        return $this->belongsTo(Session::class);
    }
}
