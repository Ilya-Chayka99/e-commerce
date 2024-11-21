<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function activeOrUpcomingRentals(): array
    {
        $currentTime = Carbon::now()->addHours(4);
        $rentals =  $this->rentals()->get()->filter(function ($rental) use ($currentTime) {

            $rentEndTime = Carbon::parse($rental->rent_time)->addMinutes($rental->minutes);

            return $rentEndTime->greaterThan($currentTime) || Carbon::parse($rental->rent_time)->greaterThan($currentTime);
        });
        return $rentals->toArray();
    }

}
