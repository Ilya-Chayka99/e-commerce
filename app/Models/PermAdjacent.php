<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class PermAdjacent extends Model
{
    protected $fillable = ['user_id', 'perm_id'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perm(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Perm::class, 'perm_id');
    }
}
