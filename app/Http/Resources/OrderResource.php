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
            'merchant' => $this->merchant,
            'email' => $this->email,
            'phone' => $this->phone,
            'total' => $this->total,
            'currency' => $this->currency,
            'financial_status' => $this->financial_status,
            'fulfillment_status' => $this->fulfillment_status,
            'order_status' => $this->order_status?->value ?? $this->order_status,
            'billing_name' => $this->billing_name,
            'billing_address_1' => $this->billing_address_1,
            'billing_address_2' => $this->billing_address_2,
            'billing_city' => $this->billing_city,
            'billing_zip' => $this->billing_zip,
            'billing_country' => $this->billing_country,
            'shipping_name' => $this->shipping_name,
            'shipping_address_1' => $this->shipping_address_1,
            'shipping_address_2' => $this->shipping_address_2,
            'shipping_city' => $this->shipping_city,
            'shipping_zip' => $this->shipping_zip,
            'shipping_province' => $this->shipping_province,
            'shipping_country' => $this->shipping_country,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'subscription_start_at' => $this->subscription_start_at ? $this->subscription_start_at->format('Y-m-d') : null,
            'subscription_end_at' => $this->subscription_end_at ? $this->subscription_end_at->format('Y-m-d') : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'paid_at' => $this->paid_at ? $this->paid_at->format('Y-m-d H:i:s') : null,
            'confirmed_at' => $this->confirmed_at ? $this->confirmed_at->format('d.m.Y H:i:s') : null,
        ];
    }
}
