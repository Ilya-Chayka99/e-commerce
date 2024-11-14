<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Computer extends Model
{
    protected $fillable = ['metadata_id', 'info_id', 'matrix_id','name'];
    protected $hidden = ['metadata_id', 'info_id', 'created_at', 'updated_at'];

    public function metadata(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(ComputerMetadata::class);
    }

    public function info(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(ComputerInfo::class);
    }

    public function matrix(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(MatrixHall::class);
    }

    public function rentals(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(ComputerRental::class);
    }

    public function getStatusAttribute(): string
    {
        $currentTime = Carbon::now();
        $currentTime = $currentTime->addHours(4);

        foreach ($this->rentals as $rental) {
            $rentStartTime = Carbon::parse($rental->rent_time);
            $rentStartTimes = $rentStartTime;
            $rentEndTime = $rentStartTimes->copy()->addMinutes($rental->minutes);

            if ($currentTime->between($rentStartTimes, $rentEndTime)) {
                return 'in_use';
//                return $currentTime;
            }
        }

        return 'available';
//        return $rentStartTime;
    }
}
