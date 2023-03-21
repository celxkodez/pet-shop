<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderStatus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'order_status_id');
    }
}
