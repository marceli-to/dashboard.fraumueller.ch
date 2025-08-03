<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;

class Get
{
  public function execute(): Collection
  {
    return Product::withCount('orders')
      ->orderBy('name')
      ->get()
      ->map(fn($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'confirmation_text' => $product->confirmation_text,
        'orders_count' => $product->orders_count,
        'created_at' => $product->created_at->format('d.m.Y H:i')
      ]);
  }
}