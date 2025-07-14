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

  public function store(Request $request)
  {
    $validated = $request->validate([
      'product_id' => 'required|exists:products,id',
      'email' => 'required|email|max:255',
      'phone' => 'nullable|string|max:255',
      'payment_method' => 'required|in:stripe,paypal,twint,invoice,creditcard',
      'merchant' => 'required|in:twint,squarespace,other',
      'total' => 'required|numeric|min:0',
      'paid_at' => 'nullable|date',
      'billing_name' => 'required|string|max:255',
      'billing_address_1' => 'required|string|max:255',
      'billing_address_2' => 'nullable|string|max:255',
      'billing_city' => 'required|string|max:255',
      'billing_zip' => 'required|string|max:255',
      'billing_country' => 'nullable|string|max:255',
      'shipping_name' => 'nullable|string|max:255',
      'shipping_address_1' => 'nullable|string|max:255',
      'shipping_address_2' => 'nullable|string|max:255',
      'shipping_city' => 'nullable|string|max:255',
      'shipping_zip' => 'nullable|string|max:255',
      'shipping_province' => 'nullable|string|max:255',
      'shipping_country' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
    ]);

    // Generate order_id for manually created orders
    $lastInternalOrder = Order::where('order_id', 'LIKE', 'IN%')->orderBy('order_id', 'desc')->first();
    
    if ($lastInternalOrder) {
        // Extract the numeric part and increment
        $lastNumber = (int)substr($lastInternalOrder->order_id, 2);
        $nextNumber = $lastNumber + 1;
    } else {
        // Start with 1 if no internal orders exist
        $nextNumber = 1;
    }
    
    $validated['order_id'] = 'IN' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

    // Set default status
    $validated['order_status'] = OrderStatus::OPEN;

    $order = Order::create($validated);

    return response()->json(new OrderResource($order), 201);
  }

  public function show(Order $order)
  {
    return response()->json(new OrderResource($order));
  }

  public function update(Request $request, Order $order)
  {
    $validated = $request->validate([
      'product_id' => 'sometimes|required|exists:products,id',
      'email' => 'sometimes|required|email|max:255',
      'phone' => 'sometimes|nullable|string|max:255',
      'payment_method' => 'sometimes|required|in:stripe,paypal,twint,invoice,creditcard',
      'merchant' => 'sometimes|required|in:twint,squarespace,other',
      'total' => 'sometimes|required|numeric|min:0',
      'paid_at' => 'sometimes|nullable|date',
      'billing_name' => 'sometimes|required|string|max:255',
      'billing_address_1' => 'sometimes|required|string|max:255',
      'billing_address_2' => 'sometimes|nullable|string|max:255',
      'billing_city' => 'sometimes|required|string|max:255',
      'billing_zip' => 'sometimes|required|string|max:255',
      'billing_country' => 'sometimes|nullable|string|max:255',
      'shipping_name' => 'sometimes|nullable|string|max:255',
      'shipping_address_1' => 'sometimes|nullable|string|max:255',
      'shipping_address_2' => 'sometimes|nullable|string|max:255',
      'shipping_city' => 'sometimes|nullable|string|max:255',
      'shipping_zip' => 'sometimes|nullable|string|max:255',
      'shipping_province' => 'sometimes|nullable|string|max:255',
      'shipping_country' => 'sometimes|nullable|string|max:255',
      'notes' => 'sometimes|nullable|string',
      'order_status' => 'sometimes|in:open,fulfilled',
    ]);

    $updatedOrder = (new UpdateOrderAction)->execute($order, $validated);

    return response()->json(new OrderResource($updatedOrder));
  }

  public function bulkUpdate(Request $request)
  {
    $validated = $request->validate([
      'order_ids' => 'required|array|min:1',
      'order_ids.*' => 'required|exists:orders,id',
      'order_status' => 'required|in:open,fulfilled',
    ]);

    try {
      $updatedCount = Order::whereIn('id', $validated['order_ids'])
        ->update(['order_status' => $validated['order_status']]);

      return response()->json([
        'success' => true,
        'message' => "Successfully updated {$updatedCount} orders",
        'updated_count' => $updatedCount
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update orders'
      ], 500);
    }
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
