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
        // Update merchant enum to include 'other'
        DB::statement("ALTER TABLE orders MODIFY COLUMN merchant ENUM('twint', 'squarespace', 'other') NULL");
        
        // Update payment_method enum to include 'invoice' and 'creditcard'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('stripe', 'paypal', 'twint', 'invoice', 'creditcard')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert merchant enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN merchant ENUM('twint', 'squarespace') NULL");
        
        // Revert payment_method enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('stripe', 'paypal', 'twint')");
    }
};
