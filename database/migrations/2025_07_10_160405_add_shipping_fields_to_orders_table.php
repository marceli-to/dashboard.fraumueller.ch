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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('billing_phone');
            $table->string('shipping_address_1')->nullable()->after('shipping_name');
            $table->string('shipping_address_2')->nullable()->after('shipping_address_1');
            $table->string('shipping_city')->nullable()->after('shipping_address_2');
            $table->string('shipping_zip', 20)->nullable()->after('shipping_city');
            $table->string('shipping_province')->nullable()->after('shipping_zip');
            $table->string('shipping_country', 100)->nullable()->after('shipping_province');
            $table->string('shipping_phone', 50)->nullable()->after('shipping_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_address_1', 
                'shipping_address_2',
                'shipping_city',
                'shipping_zip',
                'shipping_province',
                'shipping_country',
                'shipping_phone'
            ]);
        });
    }
};
