<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ResetFailedConfirmationAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:reset-failed-confirmations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset last_confirmation_attempt_at to null for orders that failed confirmation attempts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for orders with failed confirmation attempts...');

        // Find orders where confirmed_at is null but last_confirmation_attempt_at is not null
        $orders = Order::whereNull('confirmed_at')
                      ->whereNotNull('last_confirmation_attempt_at')
                      ->get();

        $count = $orders->count();

        if ($count === 0) {
            $this->info('No orders found with failed confirmation attempts.');
            return;
        }

        $this->info("Found {$count} orders with failed confirmation attempts.");

        if ($this->confirm('Do you want to reset last_confirmation_attempt_at to null for these orders?')) {
            // Reset last_confirmation_attempt_at to null
            Order::whereNull('confirmed_at')
                 ->whereNotNull('last_confirmation_attempt_at')
                 ->update(['last_confirmation_attempt_at' => null]);

            $this->info("Successfully reset {$count} orders. They will be retried on next confirmation run.");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
