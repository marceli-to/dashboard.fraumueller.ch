<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'other' to payment_method enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('stripe', 'paypal', 'twint', 'invoice', 'creditcard', 'other')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'other' from payment_method enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('stripe', 'paypal', 'twint', 'invoice', 'creditcard')");
    }
};
