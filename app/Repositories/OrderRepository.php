<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Models\Order;

class OrderRepository extends BaseRepository implements OrderRepositoryContract
{

    protected function getModelClass(): string
    {
        return Order::class;
    }
}
