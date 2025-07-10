<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Order identification
            $table->string('order_id');
            $table->enum('payment_method', ['stripe', 'paypal', 'twint']);
            
            // Customer information
            $table->string('email');
            
            // Financial details
            $table->string('currency', 3)->default('CHF');
            $table->decimal('subtotal', 8, 2);
            $table->decimal('taxes', 8, 2)->default(0.00);
            $table->decimal('shipping', 8, 2)->default(0.00);
            $table->decimal('total', 8, 2);
            
            // Order status
            $table->enum('financial_status', ['paid', 'pending', 'refunded', 'cancelled'])->default('pending');
            $table->enum('fulfillment_status', ['pending', 'fulfilled', 'cancelled'])->default('pending');
            
            // Payment references
            $table->string('payment_reference')->nullable();
            
            // Customer details
            $table->string('billing_name')->nullable();
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip', 20)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_phone', 50)->nullable();
            
            // Product information
            $table->string('product_name')->nullable();
            $table->string('product_sku', 100)->nullable();
            $table->decimal('product_price', 8, 2)->nullable();
            $table->integer('quantity')->default(1);
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('email');
            $table->index('payment_method');
            $table->index('financial_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
