<?php

namespace App\Actions\Csv;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Process
{
    protected int $imported = 0;

    protected int $skipped = 0;

    protected array $skippedRows = [];

    protected array $errors = [];

    public function execute(string $filePath): array
    {
        $this->resetCounters();

        if (! Storage::disk('public')->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $filename = basename($filePath);

        // Determine file type and process
        if (str_contains($filename, 'creditcard')) {
            $this->processCreditCardFile($fullPath);
        } elseif (str_contains($filename, 'twint')) {
            $this->processTwintFile($fullPath);
        } else {
            throw new \Exception('Unable to determine file type. Please ensure filename contains "creditcard" or "twint".');
        }

        // Move file to processed folder
        $this->moveToProcessed($filePath);

        // Log results
        $this->logResults($filename);

        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'skipped_rows' => $this->skippedRows,
            'errors' => $this->errors,
        ];
    }

    protected function resetCounters(): void
    {
        $this->imported = 0;
        $this->skipped = 0;
        $this->skippedRows = [];
        $this->errors = [];
    }

    protected function processCreditCardFile(string $path): void
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new \Exception('Could not open file.');
        }

        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($headers, $row);

                // Skip if order already exists
                if (Order::where('order_id', $data['Order ID'])->exists()) {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => $data['Order ID'],
                        'reason' => 'Order already exists',
                        'email' => $data['Email'] ?? 'N/A',
                    ];

                    continue;
                }

                Order::create([
                    'order_id' => $data['Order ID'],
                    'payment_method' => strtolower($data['Payment Method']),
                    'email' => $data['Email'],
                    'currency' => $data['Currency'],
                    'subtotal' => (float) $data['Subtotal'],
                    'taxes' => (float) $data['Taxes'],
                    'shipping' => (float) $data['Shipping'],
                    'total' => (float) $data['Total'],
                    'financial_status' => strtolower($data['Financial Status']),
                    'fulfillment_status' => strtolower($data['Fulfillment Status']),
                    'payment_reference' => $data['Payment Reference'],
                    'billing_name' => $data['Billing Name'],
                    'billing_address_1' => $data['Billing Address1'],
                    'billing_address_2' => $data['Billing Address2'],
                    'billing_city' => $data['Billing City'],
                    'billing_zip' => $data['Billing Zip'],
                    'billing_country' => $data['Billing Country'],
                    'phone' => $data['Billing Phone'],
                    'shipping_name' => $data['Shipping Name'],
                    'shipping_address_1' => $data['Shipping Address1'],
                    'shipping_address_2' => $data['Shipping Address2'],
                    'shipping_city' => $data['Shipping City'],
                    'shipping_zip' => $data['Shipping Zip'],
                    'shipping_province' => $data['Shipping Province'],
                    'shipping_country' => $data['Shipping Country'],
                    'product_name' => $this->standardizeProductName($data['Lineitem name']),
                    'product_sku' => $data['Lineitem sku'],
                    'product_price' => (float) $data['Lineitem price'],
                    'quantity' => (int) $data['Lineitem quantity'],
                    'notes' => $data['Private Notes'] ?? null,
                    'paid_at' => $data['Paid at'] ? Carbon::parse($data['Paid at']) : null,
                    'created_at' => Carbon::parse($data['Created at']),
                ]);

                $this->imported++;
            } catch (\Exception $e) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'order_id' => $data['Order ID'] ?? 'Unknown',
                    'reason' => 'Import error: '.$e->getMessage(),
                    'email' => $data['Email'] ?? 'N/A',
                ];
                $this->errors[] = $e->getMessage();
            }
        }

        fclose($handle);
    }

    protected function processTwintFile(string $path): void
    {
        $content = file_get_contents($path);

        // Convert from Windows-1252 to UTF-8 if needed
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $lines = explode("\n", $content);
        $orderCounter = 1;

        // Skip header lines and find data start
        $dataStartIndex = 0;
        foreach ($lines as $index => $line) {
            if (str_contains($line, '"2025.') && str_contains($line, '"Zahlung"')) {
                $dataStartIndex = $index;
                break;
            }
        }

        for ($i = $dataStartIndex; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            try {
                $data = str_getcsv($line, ';');

                if (count($data) < 20) {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                        'reason' => 'Incomplete row (less than 20 columns)',
                        'email' => 'N/A',
                    ];

                    continue;
                }

                $orderId = 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT);

                // Skip if order already exists
                if (Order::where('order_id', $orderId)->exists()) {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => $orderId,
                        'reason' => 'Order already exists',
                        'email' => $data[18] ?? 'N/A',
                    ];
                    $orderCounter++;

                    continue;
                }

                $total = (float) str_replace(',', '.', $data[4]);
                $fees = (float) str_replace(',', '.', $data[6]);
                $subtotal = $total - $fees;

                Order::create([
                    'order_id' => $orderId,
                    'payment_method' => 'twint',
                    'email' => $data[18] ?? '',
                    'currency' => 'CHF',
                    'subtotal' => $subtotal,
                    'taxes' => $fees,
                    'shipping' => 0.00,
                    'total' => $total,
                    'financial_status' => 'paid',
                    'fulfillment_status' => 'pending',
                    'payment_reference' => $data[9] ?? '',
                    'billing_name' => trim(($data[13] ?? '').' '.($data[14] ?? '')),
                    'billing_address_1' => $data[15] ?? '',
                    'billing_city' => $data[17] ?? '',
                    'billing_zip' => $data[16] ?? '',
                    'billing_country' => 'Switzerland',
                    'phone' => $data[19] ?? '',
                    'product_name' => $this->standardizeProductName($data[20] ?? ''),
                    'quantity' => 1,
                    'paid_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0].' '.$data[1]),
                    'created_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0].' '.$data[1]),
                ]);

                $this->imported++;
                $orderCounter++;
            } catch (\Exception $e) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'order_id' => 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                    'reason' => 'Import error: '.$e->getMessage(),
                    'email' => $data[18] ?? 'N/A',
                ];
                $this->errors[] = $e->getMessage();
            }
        }
    }

    protected function standardizeProductName(string $productName): string
    {
        $productName = trim($productName);

        // Remove trailing quotes that appear in TWINT CSV data
        $productName = rtrim($productName, '”');

        // Define mapping from various names to standardized names
        $mappings = [
            // Single issue variants
            'DIE ERSTE AUSGABE' => 'Erste Ausgabe',
            'Die erste Ausgabe' => 'Erste Ausgabe',
            'ErsteAusgabe' => 'Erste Ausgabe',
            'Jahresabo2026' => 'Jahresabo 2026',

            // Annual subscription variants
            'JAHRESABO ERSTAUSGABE' => 'Jahresabo Erstausgabe',
            'JahresaboErstausgabe' => 'Jahresabo Erstausgabe',
            'JAHRESABO 2026' => 'Jahresabo 2026',
            'JAHRESABO' => 'Jahresabo',

            // Gift subscription
            'Geschenk-Abo' => 'Geschenk-Abo',
        ];

        return $mappings[$productName] ?? $productName;
    }

    protected function moveToProcessed(string $filePath): void
    {
        $processedPath = str_replace('csv/pending', 'csv/processed', $filePath);

        // Ensure processed directory exists
        Storage::disk('public')->makeDirectory(dirname($processedPath));

        // Move file
        Storage::disk('public')->move($filePath, $processedPath);
    }

    protected function logResults(string $filename): void
    {
        Log::info("CSV Import completed - {$filename}", [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'skipped_rows' => $this->skippedRows,
            'errors' => $this->errors,
        ]);
    }
}
