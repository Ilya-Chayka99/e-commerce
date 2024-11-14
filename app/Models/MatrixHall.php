<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class MatrixHall extends Model
{
    protected $fillable = ['x','y','width','height','info'];

    public function info(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Computer::class);
    }
}
