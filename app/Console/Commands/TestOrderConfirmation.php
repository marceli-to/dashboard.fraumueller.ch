<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestOrderConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:test-confirmation {email? : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the OrderConfirmation notification with sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Environment safety check
        if (!$this->isTestingEnvironmentSafe()) {
            $this->error('This command can only be run in local, testing, or staging environments.');
            $this->error('Current environment: ' . app()->environment());
            return 1;
        }

        $email = $this->argument('email') ?? $this->ask('What email address should receive the test notification?');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');
            return 1;
        }

        $this->info('Testing OrderConfirmation notification...');
        $this->info('Environment: ' . app()->environment());
        $this->info('Sending to: ' . $email);

        // Create sample order data
        $sampleData = $this->createSampleOrderData();

        try {
            // Send notification using the Notification facade
            Notification::route('mail', $email)->notify(new OrderConfirmation($sampleData));

            $this->info('✅ Test notification sent successfully!');
            $this->info('Check your email at: ' . $email);
            
            if (app()->environment('local')) {
                $this->info('💡 If using local mail testing (like MailHog), check your local mail catcher.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to send test notification:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Check if the current environment is safe for testing
     */
    private function isTestingEnvironmentSafe(): bool
    {
        $allowedEnvironments = ['local', 'testing', 'staging', 'development'];
        return in_array(app()->environment(), $allowedEnvironments);
    }

    /**
     * Create sample order data for testing
     */
    private function createSampleOrderData(): array
    {
        // Try to get a real product, or create sample data
        $product = Product::first();
        
        return [
            'order_id' => 'TEST-' . now()->format('YmdHis'),
            'product_name' => $product ? $product->name : 'Test Product',
            'email' => 'test@example.com',
            'phone' => '+41 79 123 45 67',
            'total' => '99.90',
            'payment_method' => 'creditcard',
            'merchant' => 'squarespace',
            'billing_name' => 'Max Mustermann',
            'billing_address_1' => 'Musterstrasse 123',
            'billing_address_2' => '',
            'billing_city' => 'Zürich',
            'billing_zip' => '8001',
            'billing_country' => 'Schweiz',
            'shipping_name' => 'Max Mustermann',
            'shipping_address_1' => 'Musterstrasse 123',
            'shipping_address_2' => '',
            'shipping_city' => 'Zürich',
            'shipping_zip' => '8001',
            'shipping_province' => 'ZH',
            'shipping_country' => 'Schweiz',
            'notes' => 'Dies ist eine Test-Bestellung für die Notification.',
            'order_status' => 'open',
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
