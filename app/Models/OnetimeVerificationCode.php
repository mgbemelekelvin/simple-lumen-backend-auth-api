<?php

namespace App\Models;

use App\Services\GeneralService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnetimeVerificationCode extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = [
        'code', 'user_id', 'type', 'status'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user(){
        return $this->belongsTo(User_detail::class);
    }

    public function onetimeVerificationType(){
        return $this->belongsTo(OnetimeVerificationType::class, 'type');
    }

//    public function setCodeAttribute(){
//        $this->attributes['code'] = $this->uniqueCode();
//    }
//
//    private function uniqueCode(){
//        $code = GeneralService::generateRandomString(OnetimeVerificationType::defaultCodeLength());
//        while(OnetimeVerificationCode::where('code', $code)->first()){
//            return $this->uniqueCode();
//        }
//        return $code;
//    }
}
