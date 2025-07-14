<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;

class Get
{
    public function execute(): Collection
    {
        return Product::orderBy('name')
            ->get()
            ->map(fn($product) => [
                'value' => $product->id,
                'label' => $product->name
            ]);
    }
}