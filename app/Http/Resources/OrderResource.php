<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'order_id' => $this->order_id,
      'payment_method' => $this->payment_method,
      'email' => $this->email,
      'total' => $this->total,
      'currency' => $this->currency,
      'financial_status' => $this->financial_status,
      'fulfillment_status' => $this->fulfillment_status,
      'billing_name' => $this->billing_name,
      'billing_city' => $this->billing_city,
      'billing_country' => $this->billing_country,
      'product_name' => $this->product_name,
      'quantity' => $this->quantity,
      'notes' => $this->notes,
      'created_at' => $this->created_at->format('Y-m-d H:i:s'),
      'paid_at' => $this->paid_at ? $this->paid_at->format('Y-m-d H:i:s') : null,
    ];
  }
}