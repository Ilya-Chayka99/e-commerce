<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = ['from', 'to', 'coefficient'];
}
