<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ComputerInfo extends Model
{
    protected $fillable = ['RAM', 'processor', 'GPU', 'monitor', 'headphones', 'mouse', 'keyboard'];
}
