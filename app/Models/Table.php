<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['metadata_id', 'info_id', 'matrix_id','name'];
    protected $hidden = ['metadata_id', 'info_id', 'created_at', 'updated_at'];

    public function metadata(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(TableMetadata::class);
    }

    public function info(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(TableInfo::class);
    }

    public function matrix(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(MatrixHall::class);
    }

    public function rentals(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(TableRental::class);
    }

    public function getStatusAttribute(): string
    {
        $currentTime = getdate(strtotime('now'));
        $currentTimeUnix = mktime($currentTime['hours'], $currentTime['minutes'], 0, $currentTime['mon'], $currentTime['mday'], $currentTime['year']);
        foreach ($this->rentals as $rental) {
            $rentStartTime = getdate(strtotime($rental->rent_time));
            $rentStartTimeUnix = mktime($rentStartTime['hours'], $rentStartTime['minutes'], 0, $rentStartTime['mon'], $rentStartTime['mday'], $rentStartTime['year']);
            $rentEndTimeUnix = $rentStartTimeUnix + ($rental->minutes * 60);
            if ($currentTimeUnix >= $rentStartTimeUnix && $currentTimeUnix <= $rentEndTimeUnix) {
                return 'in_use';
            }
        }

        return 'available';
    }
}
