<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = ['user_id', 'payment_type', 'quantity', 'payment_date', 'payment_hash'];
    protected $hidden = ['id','user_id'];
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\MongoDB\Laravel\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
