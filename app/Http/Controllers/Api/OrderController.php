<?php
namespace App\Http\Controllers\Api;
use App\Actions\Order\Get as GetOrdersAction;
use App\Actions\Order\Update as UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Enums\OrderStatus;
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
      'email' => 'sometimes|required|email|max:255',
      'billing_name' => 'sometimes|required|string|max:255',
      'billing_address_1' => 'sometimes|required|string|max:255',
      'billing_city' => 'sometimes|required|string|max:255',
      'billing_zip' => 'sometimes|required|string|max:255',
      'billing_country' => 'sometimes|string|max:255',
      'order_status' => 'sometimes|in:open,fulfilled',
      'product_id' => 'sometimes|required|exists:products,id',
      'phone' => 'sometimes|nullable|string|max:255',
      'billing_address_2' => 'sometimes|nullable|string|max:255',
      'shipping_name' => 'sometimes|nullable|string|max:255',
      'shipping_address_1' => 'sometimes|nullable|string|max:255',
      'shipping_address_2' => 'sometimes|nullable|string|max:255',
      'shipping_city' => 'sometimes|nullable|string|max:255',
      'shipping_zip' => 'sometimes|nullable|string|max:255',
      'shipping_province' => 'sometimes|nullable|string|max:255',
      'shipping_country' => 'sometimes|nullable|string|max:255',
      'notes' => 'sometimes|nullable|string',
    ]);

    $updatedOrder = (new UpdateOrderAction)->execute($order, $validated);

    return response()->json(new OrderResource($updatedOrder));
  }

  public function destroy(Order $order)
  {
    $order->delete();

    return response()->json([
      'success' => true,
      'message' => 'Order deleted successfully'
    ]);
  }

}
