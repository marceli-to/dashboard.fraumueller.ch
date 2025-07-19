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
      'billing_name' => 'sometimes|required|string|max:255',
      'billing_address_1' => 'sometimes|required|string|max:255',
      'billing_address_2' => 'sometimes|nullable|string|max:255',
      'billing_city' => 'sometimes|required|string|max:255',
      'billing_zip' => 'sometimes|required|string|max:255',
      'billing_country' => 'sometimes|nullable|string|max:255',
      'notes' => 'sometimes|nullable|string',
    ]);

    $updatedOrder = (new UpdateOrderAction)->execute($order, $validated);

    return response()->json(new OrderResource($updatedOrder));
  }

  public function bulkUpdate(Request $request)
  {
    $validated = $request->validate([
      'order_ids' => 'required|array|min:1',
      'order_ids.*' => 'required|exists:orders,id',
      'order_status' => 'sometimes|in:open,fulfilled',
      'notes' => 'sometimes|nullable|string',
      'product_id' => 'sometimes|required|exists:products,id',
    ]);

    try {
      $updateData = [];
      
      // Add fields to update based on what's provided
      if (isset($validated['order_status'])) {
        $updateData['order_status'] = $validated['order_status'];
      }
      
      if (isset($validated['product_id'])) {
        $updateData['product_id'] = $validated['product_id'];
      }
      
      if (array_key_exists('notes', $validated)) {
        // For notes, we need to append to existing content instead of overwriting
        $newNote = $validated['notes'];
        if (!empty($newNote)) {
          // Update each order individually to append notes
          Order::whereIn('id', $validated['order_ids'])->get()->each(function ($order) use ($newNote) {
            $existingNotes = $order->notes;
            if (!empty($existingNotes)) {
              $order->notes = $newNote . "\n" . $existingNotes;
            } else {
              $order->notes = $newNote;
            }
            $order->save();
          });
          
          // Remove notes from batch update data since we handled it individually
          unset($updateData['notes']);
        }
      }
      
      $updatedCount = 0;
      
      // Only run batch update if there are fields to update
      if (!empty($updateData)) {
        $updatedCount = Order::whereIn('id', $validated['order_ids'])
          ->update($updateData);
      } else {
        // If no batch update, count the orders for response
        $updatedCount = count($validated['order_ids']);
      }

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
