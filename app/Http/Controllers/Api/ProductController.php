<?php
namespace App\Http\Controllers\Api;

use App\Actions\Product\Get as GetProductsAction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
  public function index()
  {
    return response()->json([
      'data' => (new GetProductsAction)->execute()
    ]);
  }

  public function show($id)
  {
    $product = Product::findOrFail($id);
    
    return response()->json([
      'id' => $product->id,
      'name' => $product->name,
      'confirmation_text' => $product->confirmation_text,
      'created_at' => $product->created_at->format('d.m.Y H:i'),
      'updated_at' => $product->updated_at->format('d.m.Y H:i')
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:products,name',
      'confirmation_text' => 'nullable|string'
    ]);

    $product = Product::create([
      'name' => $request->input('name'),
      'confirmation_text' => $request->input('confirmation_text')
    ]);

    return response()->json([
      'id' => $product->id,
      'name' => $product->name,
      'confirmation_text' => $product->confirmation_text,
      'created_at' => $product->created_at->format('d.m.Y H:i'),
      'updated_at' => $product->updated_at->format('d.m.Y H:i')
    ], 201);
  }

  public function update(Request $request, $id)
  {
    $product = Product::findOrFail($id);
    
    $request->validate([
      'name' => 'required|string|max:255|unique:products,name,' . $id,
      'confirmation_text' => 'nullable|string'
    ]);

    $product->update([
      'name' => $request->input('name'),
      'confirmation_text' => $request->input('confirmation_text')
    ]);

    return response()->json([
      'id' => $product->id,
      'name' => $product->name,
      'confirmation_text' => $product->confirmation_text,
      'created_at' => $product->created_at->format('d.m.Y H:i'),
      'updated_at' => $product->updated_at->format('d.m.Y H:i')
    ]);
  }

  public function destroy($id)
  {
    $product = Product::findOrFail($id);
    
    // Check if product has orders
    if ($product->orders()->count() > 0) {
      return response()->json([
        'message' => 'Produkt kann nicht gelöscht werden, da es Bestellungen zugeordnet ist.'
      ], 422);
    }
    
    $product->delete();

    return response()->json([
      'message' => 'Produkt wurde erfolgreich gelöscht.'
    ]);
  }
}
