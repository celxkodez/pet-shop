<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Order
 *
 * @property-read \App\Models\OrderStatus|null $orderStatus
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'shipped_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! isset($model->uuid)) {
                $model->uuid = \Str::uuid()->toString();
            }
        });

        static::saving(function ($model) {
            $model->delivery_fee = 15.00;

            $products = json_decode($model->products, true);

            $products = Product::whereIn('uuid', collect($products)
                ->pluck('uuid')
                ->toArray()
            )
                ->select(['price', 'uuid'])
                ->get();

            $productsAmount = $products->sum('price');

            $model->amount = $productsAmount;

            if ($model->amount > 500) {
                $model->delivery_fee = 0.00;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }
}
