<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Order\Get as GetOrdersAction;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
  public function get()
  {
    return response()->json(
      OrderResource::collection(
        (new GetOrdersAction())->execute()
      )
    );
  }
}