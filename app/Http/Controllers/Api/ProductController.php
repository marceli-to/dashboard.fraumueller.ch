<?php
namespace App\Http\Controllers\Api;

use App\Actions\Product\Get as GetProductsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function index()
  {
    return response()->json([
      'data' => (new GetProductsAction)->execute()
    ]);
  }
}
