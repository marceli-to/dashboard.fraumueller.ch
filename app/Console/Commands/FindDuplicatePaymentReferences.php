<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FindDuplicatePaymentReferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:find-duplicates {--show-details : Show detailed information for each duplicate} {--export-csv : Export results to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find duplicate payment_reference values in the orders table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for duplicate payment references...');

        $duplicates = DB::table('orders')
            ->select('payment_reference', DB::raw('COUNT(*) as count'))
            ->whereNotNull('payment_reference')
            ->where('payment_reference', '!=', '')
            ->groupBy('payment_reference')
            ->having('count', '>', 1)
            ->orderBy('count', 'desc')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate payment references found.');
            return 0;
        }

        $this->warn("Found {$duplicates->count()} duplicate payment references:");
        $this->newLine();

        foreach ($duplicates as $duplicate) {
            $this->line("Payment Reference: <comment>{$duplicate->payment_reference}</comment> (appears <error>{$duplicate->count}</error> times)");
            
            if ($this->option('show-details')) {
                $orders = DB::table('orders')
                    ->where('payment_reference', $duplicate->payment_reference)
                    ->select('id', 'merchant', 'email', 'created_at', 'updated_at')
                    ->orderBy('created_at')
                    ->get();

                foreach ($orders as $order) {
                    $this->line("  - Order ID: {$order->id}, Merchant: {$order->merchant}, Email: {$order->email}, Created: {$order->created_at}");
                }
                $this->newLine();
            }
        }

        $totalDuplicateRecords = $duplicates->sum('count');
        $this->newLine();
        $this->warn("Total records with duplicate payment references: {$totalDuplicateRecords}");

        // Export to CSV if requested
        if ($this->option('export-csv')) {
            $this->exportToCsv($duplicates);
        }

        return 0;
    }

    protected function exportToCsv($duplicates): void
    {
        $filename = 'duplicates_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = 'csv/export/' . $filename;
        
        // Ensure export directory exists
        Storage::disk('public')->makeDirectory('csv/export');
        
        $csvData = [];
        $csvData[] = ['Payment Reference', 'Count', 'Order ID', 'Merchant', 'Email', 'Created At'];
        
        foreach ($duplicates as $duplicate) {
            $orders = DB::table('orders')
                ->where('payment_reference', $duplicate->payment_reference)
                ->select('id', 'merchant', 'email', 'created_at')
                ->orderBy('created_at')
                ->get();
                
            foreach ($orders as $order) {
                $csvData[] = [
                    $duplicate->payment_reference,
                    $duplicate->count,
                    $order->id,
                    $order->merchant,
                    $order->email,
                    $order->created_at
                ];
            }
        }
        
        $handle = fopen(Storage::disk('public')->path($filepath), 'w');
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        $this->newLine();
        $this->info("CSV export saved to: storage/app/public/{$filepath}");
    }
}
