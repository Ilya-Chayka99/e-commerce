<?php

namespace App\Models;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class TableMetadata extends Model
{
    protected $fillable = ['name', 'price'];
    protected $hidden = ['id','updated_at','created_at'];

    public function computers(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(Table::class);
    }
}
