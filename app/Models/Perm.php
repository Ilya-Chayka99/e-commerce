<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class Perm extends Model
{
    protected $fillable = ['name', 'edit_tarif', 'edit_computer', 'add_perms', 'free_rentals'];

    public function perm(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(Perm::class, 'perm_id');
    }
    public function users(): \MongoDB\Laravel\Relations\BelongsToMany|\Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'perms_adjacents');
    }
}
