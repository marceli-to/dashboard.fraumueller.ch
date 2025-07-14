<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OrderStatus;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_id',
        'payment_method',
        'email',
        'currency',
        'subtotal',
        'taxes',
        'shipping',
        'total',
        'financial_status',
        'fulfillment_status',
        'payment_reference',
        'billing_name',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'phone',
        'shipping_name',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_zip',
        'shipping_province',
        'shipping_country',
        'product_id',
        'product_sku',
        'product_price',
        'quantity',
        'notes',
        'paid_at',
        'confirmed_at',
        'order_status',
        'merchant',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'taxes' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'product_price' => 'decimal:2',
        'quantity' => 'integer',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'order_status' => OrderStatus::class,
    ];

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopePaid($query)
    {
        return $query->where('financial_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('financial_status', 'pending');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
