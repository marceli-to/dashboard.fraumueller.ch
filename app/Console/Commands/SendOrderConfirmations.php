<?php
namespace App\Console\Commands;
use App\Actions\Notification\Confirmation as ConfirmationAction;
use Illuminate\Console\Command;

class SendOrderConfirmations extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'orders:send-confirmations';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send confirmation notifications to orders that have not been confirmed yet';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    (new ConfirmationAction)->execute();
  }

}