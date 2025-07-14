<?php

namespace App\Actions\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class Get
{
    /**
     * Get all orders sorted by created date (descending)
     */
    public function execute(): Collection
    {
        return Order::with('product')->orderByDesc('paid_at')->get();
    }
}
