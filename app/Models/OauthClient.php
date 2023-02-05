<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthClient extends Model
{
    use HasFactory;

    public function views($view, $option)
    {
        return [
            'name' => json_decode($this->name)->name,
            'appCountry' => json_decode($this->name)->country,
            'appCurrency' => json_decode($this->name)->currency,
            'secret'=>$this->secret,
            'redirect'=>$this->redirect,
        ];
    }
}
