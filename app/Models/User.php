<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Facades\Hash;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model
{
    protected $connection = 'mongodb';
    protected string $collection = 'users';

    public $fillable = ['vkID', 'token', 'money'];
    protected $hidden = ['id'];


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
