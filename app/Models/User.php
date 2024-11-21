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
        $currentTime = getdate(strtotime('now'));
        $rentals = $this->rentals()->get()->filter(function ($rental) use ($currentTime) {

            $rentStartTime = getdate(strtotime($rental->rent_time));
            $rentEndTime = getdate(strtotime($rental->rent_time) + ($rental->minutes * 60));

            $rentStartTimeUnix = mktime($rentStartTime['hours'], $rentStartTime['minutes'], 0, $rentStartTime['mon'], $rentStartTime['mday'], $rentStartTime['year']);
            $rentEndTimeUnix = mktime($rentEndTime['hours'], $rentEndTime['minutes'], 0, $rentEndTime['mon'], $rentEndTime['mday'], $rentEndTime['year']);

            $currentTimeUnix = mktime($currentTime['hours'], $currentTime['minutes'], 0, $currentTime['mon'], $currentTime['mday'], $currentTime['year']);

            // Проверяем, если аренда еще не закончена или еще не началась
            return $rentEndTimeUnix > $currentTimeUnix || $rentStartTimeUnix > $currentTimeUnix;
        });

        return $rentals->toArray();
    }

}
