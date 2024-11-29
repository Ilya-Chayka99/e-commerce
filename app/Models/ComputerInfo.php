<?php

namespace App\Models;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class ComputerInfo extends Model
{
    protected $fillable = ['RAM', 'processor', 'GPU', 'monitor', 'headphones', 'mouse', 'keyboard'];
    protected $hidden = ['id','updated_at','created_at'];
    public function computers(): \Illuminate\Database\Eloquent\Relations\HasMany|\MongoDB\Laravel\Relations\HasMany
    {
        return $this->hasMany(Computer::class);
    }
}
