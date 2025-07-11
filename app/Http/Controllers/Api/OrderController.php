<?php
namespace App\Http\Controllers\Api;
use App\Actions\Order\Get as GetOrdersAction;
use App\Actions\Order\Update as UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  public function get()
  {
    return response()->json(
      OrderResource::collection(
        (new GetOrdersAction)->execute()
      )
    );
  }

  public function show(Order $order)
  {
    return response()->json(new OrderResource($order));
  }

  public function update(Request $request, Order $order)
  {
    $validated = $request->validate([
      'email' => 'required|email|max:255',
      'billing_name' => 'required|string|max:255',
      'billing_address_1' => 'required|string|max:255',
      'billing_city' => 'required|string|max:255',
      'billing_zip' => 'required|string|max:255',
    ]);

    $updatedOrder = (new UpdateOrderAction)->execute($order, $validated);

    return response()->json(new OrderResource($updatedOrder));
  }
}
