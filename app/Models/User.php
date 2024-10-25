<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use MongoDB\Laravel\Eloquent\Model;

class User extends Model
{
    protected string $collection = 'users';

    protected $fillable = ['login', 'password', 'money'];

    public function userInfo(): \Illuminate\Database\Eloquent\Relations\HasOne|\MongoDB\Laravel\Relations\HasOne
    {
        return $this->hasOne(UserInfo::class);
    }

    public function rentals(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(ComputerRental::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }
    public static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory|\Database\Factories\UserFactory
    {
        return \Database\Factories\UserFactory::new();
    }
}
