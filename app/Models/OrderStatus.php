<?php

namespace App\Models;

use Celestine\NotificationServices\Events\OrderStatusEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrderStatus
 *
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|OrderStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderStatus query()
 * @mixin \Eloquent
 */
class OrderStatus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! isset($model->uuid)) {
                $model->uuid = \Str::uuid()->toString();
            }
        });

        static::created(function ($model) {

            OrderStatusEvent::dispatch($model->uuid, $model->title, now()->toString());
        });
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'order_status_id');
    }
}
