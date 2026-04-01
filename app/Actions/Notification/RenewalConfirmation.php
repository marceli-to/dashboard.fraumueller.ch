<?php
namespace App\Actions\Notification;
use App\Models\Order;
use App\Models\OrderLog;
use App\Notifications\RenewalConfirmation as RenewalConfirmationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class RenewalConfirmation
{
  public function execute(): void
  {
    $orders = Order::whereNotNull('renewed_at')
      ->whereNull('renewal_confirmed_at')
      ->whereNotNull('email')
      ->limit(3)
      ->get();

    foreach ($orders as $order) {
      try {
        Notification::route('mail', $order->email)->notify(new RenewalConfirmationNotification($order));

        $order->update([
          'renewal_confirmed_at' => now(),
        ]);

        OrderLog::updateOrCreate(
          ['order_id' => $order->order_id],
          [
            'email' => $order->email,
            'info' => 'Erneuerungsbestätigung versendet',
            'status' => 'success'
          ]
        );
      }
      catch (\Exception $e) {
        Log::error("Failed to send renewal confirmation", [
          'order_id' => $order->order_id,
          'email' => $order->email,
          'error' => $e->getMessage()
        ]);

        OrderLog::updateOrCreate(
          ['order_id' => $order->order_id],
          [
            'email' => $order->email,
            'info' => 'Fehler beim Versenden der Erneuerungsbestätigung: ' . $e->getMessage(),
            'status' => 'error'
          ]
        );
      }
    }
  }
}
