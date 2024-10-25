<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class UserInfo extends Model
{
    protected string $collection = 'user_infos';

    protected $fillable = [
        'user_id', 'name', 'last_name', 'middle_name', 'birthday', 'phone'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function newFactory()
    {
        return \Database\Factories\UserInfoFactory::new();
    }
}
