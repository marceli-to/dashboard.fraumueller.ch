<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SendTestNotification
{
    public function execute(int $productId): array
    {
        $product = Product::findOrFail($productId);
        
        if (empty($product->confirmation_text)) {
            throw new \Exception('Produkt hat keinen Bestätigungstext.');
        }

        $user = Auth::user();
        if (!$user || !$user->email) {
            throw new \Exception('Kein eingeloggter Benutzer gefunden.');
        }

        try {
            // Create a mock order object for the notification
            $mockOrder = (object) [
                'product' => $product,
                'email' => $user->email
            ];

            // Send notification using the existing OrderConfirmation notification
            Notification::route('mail', $user->email)->notify(new OrderConfirmation($mockOrder));

            return [
                'success' => true,
                'message' => 'Test-Benachrichtigung erfolgreich gesendet an ' . $user->email
            ];
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Senden der E-Mail: ' . $e->getMessage());
        }
    }
}