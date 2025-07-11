<?php
namespace App\Actions\Order;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class Get
{
  /**
   * Get all orders sorted by created date (descending)
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function execute(): Collection
  {
    return Order::orderByDesc('paid_at')->get();
  }
}