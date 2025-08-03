<?php
namespace App\Actions\Notification;
use App\Models\Order;
use App\Models\OrderLog;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class Confirmation
{
  /**
   * Execute confirmation notifications for unconfirmed orders
   */
  public function execute(): void
  {
    // Get all orders where confirmed_at is NULL and haven't been attempted since last update
    $orders = Order::with('product')
      ->whereNull('confirmed_at')
      ->where('merchant', 'twint')
      ->whereNotNull('email')
      ->where(function ($query) {
        $query->whereNull('last_confirmation_attempt_at')->orWhereColumn('last_confirmation_attempt_at', '<', 'updated_at');
      })
      ->limit(3)
      ->get();

    foreach ($orders as $order) {
      try {
        // Update attempt timestamp at the start of processing
        $order->update(['last_confirmation_attempt_at' => now()]);
        
        // Check if product has a valid template key
        $productKey = $this->getProductKey($order);
        
        if (!$productKey) {
          // Log invalid product (update if exists, create if not)
          OrderLog::updateOrCreate(
            ['order_id' => $order->order_id],
            [
              'email' => $order->email,
              'info' => 'Kein Template für «'.$order->product->name.'» gefunden',
              'status' => 'error'
            ]
          );
          continue; // Skip this order
        }

        // Get template config and add the key for mail template conditional logic
        $template = config("order_confirmation.templates.{$productKey}");
        $template['key'] = $productKey;
        
        // Send confirmation notification with template
        Notification::route('mail', $order->email)->notify(new OrderConfirmation($order, $template));

        // Only update confirmed_at and log success if notification was sent successfully
        $order->update(['confirmed_at' => now()]);

        // Log successful confirmation
        OrderLog::updateOrCreate(
          ['order_id' => $order->order_id],
          [
            'email' => $order->email,
            'info' => 'Bestätigungsmail versendet',
            'status' => 'success'
          ]
        );
      } 
      catch (\Exception $e) {        
        Log::error("Failed to send order confirmation", [
          'order_id' => $order->order_id,
          'email' => $order->email,
          'error' => $e->getMessage()
        ]);

        // Log failed confirmation attempt
        OrderLog::updateOrCreate(
          ['order_id' => $order->order_id],
          [
            'email' => $order->email,
            'info' => 'Fehler beim Versenden der Bestätigungsmail: ' . $e->getMessage(),
            'status' => 'error'
          ]
        );
      }
    }
  }

  /**
   * Get the template key for the order's product
   */
  private function getProductKey(Order $order): ?string
  {
    if (!$order->product || !$order->product->name) {
      return null;
    }

    $productName = $order->product->name;
    $templates = config('order_confirmation.templates', []);
    
    // Find matching template key based on product name
    foreach ($templates as $key => $template)
    {
      if (strtolower($productName) === strtolower($template['name'])) {
        return $key;
      }
    }

    return null;
  }
}