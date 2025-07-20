<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Actions\Notification\Testmail as TestmailAction;

class Testmail extends Command
{
  protected $signature = 'mail:testmail';

  protected $description = 'Send a test email';

  public function handle()
  {
    (new TestmailAction)->execute();
  }
}