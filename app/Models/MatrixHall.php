<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class MatrixHall extends Model
{
    protected $fillable = ['x','y','width','height'];

    public function computer(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

}
