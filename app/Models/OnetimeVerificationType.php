<?php

namespace App\Models;

use Database\Seeders\OnetimeVerificationTypeSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnetimeVerificationType extends Model
{
    use HasFactory, softDeletes;

    public static function defaultExpiry(){
        return 10;
    }
    public static function defaultCodeLength(){
        return 6;
    }
    protected $fillable = [
        'name', 'expiry_time'
    ];
}
