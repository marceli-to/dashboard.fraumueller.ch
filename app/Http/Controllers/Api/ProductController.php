<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        
        return response()->json([
            'data' => $products->map(fn($product) => [
                'value' => $product->id,
                'label' => $product->name
            ])
        ]);
    }
}
