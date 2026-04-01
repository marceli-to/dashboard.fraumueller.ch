<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ImportRenewals extends Command
{
    protected $signature = 'import:renewals {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Import renewal dates from renewals.json and set renewed_at on matching orders';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $path = base_path('renewals.json');

        if (!file_exists($path)) {
            $this->error('renewals.json not found.');
            return 1;
        }

        $renewals = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON in renewals.json.');
            return 1;
        }

        if ($dryRun) {
            $this->info('[DRY RUN] No changes will be made.');
        }

        $updated = 0;
        $notFound = 0;
        $notFoundLog = [];

        foreach ($renewals as $renewal) {
            $email = $renewal['customer_email'];
            $createdAt = $renewal['created'];

            $order = Order::where('email', $email)->first();

            if (!$order) {
                $this->warn("No order found for: {$email}");
                $notFoundLog[] = "{$createdAt} - {$email}";
                $notFound++;
                continue;
            }

            if ($dryRun) {
                $this->line("Would set renewed_at = {$createdAt} for order #{$order->order_id} ({$email})");
            } else {
                $order->renewed_at = $createdAt;
                $order->save();
                $this->line("Updated order #{$order->order_id} ({$email}) → renewed_at = {$createdAt}");
            }

            $updated++;
        }

        if (!empty($notFoundLog)) {
            $logPath = storage_path('logs/renewals-not-found.log');
            file_put_contents($logPath, implode("\n", $notFoundLog) . "\n");
            $this->warn("Log written to: {$logPath}");
        }

        $this->newLine();
        $this->info("Done. Updated: {$updated}, Not found: {$notFound}");

        return 0;
    }
}
