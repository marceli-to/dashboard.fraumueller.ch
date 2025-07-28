<?php

namespace App\Console\Commands;

use App\Actions\Database\Backup as BackupAction;
use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full database backup as .tar.gz file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      (new BackupAction())->execute();
    }
}
