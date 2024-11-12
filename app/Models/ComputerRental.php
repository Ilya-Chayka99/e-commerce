<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ComputerRental extends Model
{
    protected $fillable = ['computer_id', 'user_id', 'rent_time', 'minutes', 'end_price'];
    protected $hidden = ['computer_id', 'user_id'];

    public function computer(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Computer::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
