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
        // Check if product has confirmation text
        if (!$order->product || empty($order->product->confirmation_text)) {
          // Log missing confirmation text
          OrderLog::updateOrCreate(
            ['order_id' => $order->order_id],
            [
              'email' => $order->email,
              'info' => 'Kein Bestätigungstext für «'.$order->product->name.'» hinterlegt',
              'status' => 'error'
            ]
          );

          $order->update([
            'last_confirmation_attempt_at' => now()
          ]);

          continue; // Skip this order
        }
        
        // Send confirmation notification with order
        Notification::route('mail', $order->email)->notify(new OrderConfirmation($order));

        // Only update confirmed_at, attempt timestamp and log success if notification was sent successfully
        $order->update([
          'confirmed_at' => now(),
          'last_confirmation_attempt_at' => now()
        ]);

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

        // Log failed confirmation attempt (but don't update last_confirmation_attempt_at to allow retries)
        OrderLog::updateOrCreate(
          ['order_id' => $order->order_id],
          [
            'email' => $order->email,
            'info' => 'Fehler beim Versenden der Bestätigungsmail: ' . $e->getMessage(),
            'status' => 'error'
          ]
        );

        $order->update([
          'last_confirmation_attempt_at' => now()
        ]);
        
      }
    }
  }

}