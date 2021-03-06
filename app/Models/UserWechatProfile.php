<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWechatProfile extends Model
{
    protected $fillable = [
        'user_id',
        'openid',
        'nickname',
        'sex',
        'language',
        'city',
        'province',
        'country',
        'headimgurl',
        'unionid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
