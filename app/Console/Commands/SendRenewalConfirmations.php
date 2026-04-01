<?php
namespace App\Console\Commands;
use App\Actions\Notification\RenewalConfirmation;
use Illuminate\Console\Command;

class SendRenewalConfirmations extends Command
{
  protected $signature = 'orders:send-renewal-confirmations';

  protected $description = 'Send confirmation notifications to renewed orders that have not been confirmed yet';

  public function handle()
  {
    (new RenewalConfirmation)->execute();
  }
}
