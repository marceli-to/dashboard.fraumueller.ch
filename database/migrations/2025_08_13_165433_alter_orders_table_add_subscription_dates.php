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
        $table->timestamp('subscription_start_at')->after('last_confirmation_attempt_at')->nullable();
        $table->timestamp('subscription_end_at')->after('subscription_start_at')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('subscription_start_at');
        $table->dropColumn('subscription_end_at');
      });
    }
};
