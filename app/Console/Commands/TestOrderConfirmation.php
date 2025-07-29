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
    protected $signature = 'order:test-confirmation';

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
      $order = Order::first();
      $template = config("order_confirmation.templates.erste-ausgabe");
      $template['key'] = 'erste-ausgabe';
      Notification::route('mail', 'm@marceli.to')->notify(new OrderConfirmation($order, $template));
    }

}
