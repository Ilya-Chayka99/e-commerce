<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Perm extends Model
{
    protected $fillable = ['name', 'edit_tarif', 'edit_computer', 'add_perms', 'free_rentals'];

    public function users(): \MongoDB\Laravel\Relations\BelongsToMany|\Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'perms_adjacents');
    }
}