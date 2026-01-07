<?php

namespace App\Actions\Csv;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Process
{
    protected int $imported = 0;

    protected int $skipped = 0;

    protected array $skippedRows = [];

    protected array $errors = [];

    public function execute(string $filePath, ?string $merchant = null): array
    {
        $this->resetCounters();

        if (! Storage::disk('public')->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $filename = basename($filePath);

        // Determine processing method based on merchant parameter or filename
        if ($merchant) {
            if ($merchant === 'squarespace') {
                $this->processCreditCardFile($fullPath, $merchant);
            } elseif ($merchant === 'twint') {
                $this->processTwintFile($fullPath, $merchant);
            } else {
                throw new \Exception("Unsupported merchant: {$merchant}");
            }
        } else {
            // Fallback to filename-based detection for backward compatibility
            if (str_contains($filename, 'creditcard')) {
                $this->processCreditCardFile($fullPath, 'squarespace');
            } elseif (str_contains($filename, 'twint')) {
                $this->processTwintFile($fullPath, 'twint');
            } else {
                throw new \Exception('Unable to determine file type. Please ensure filename contains "creditcard" or "twint", or specify merchant parameter.');
            }
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

    protected function processCreditCardFile(string $path, string $merchant): void
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new \Exception('Could not open file.');
        }

        $headers = fgetcsv($handle);

        // Read all CSV data into memory
        $allRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $allRows[] = $row;
        }
        fclose($handle);

        // Group rows by Order ID
        $groupedOrders = $this->groupOrderRows($headers, $allRows);

        // Process each grouped order
        foreach ($groupedOrders as $orderData) {
            $data = $orderData['main_data'];

            // Skip if financial status is refunded
            if (strtolower($data['Financial Status']) === 'refunded') {
                $this->skipped++;
                $this->skippedRows[] = [
                    'order_id' => $data['Order ID'],
                    'reason' => 'Order refunded',
                    'email' => $data['Email'] ?? 'N/A',
                ];

                continue;
            }

            // Create separate Order record for each product
            foreach ($orderData['products'] as $product) {
                // Skip if this specific product already exists for this payment reference
                if (Order::where('payment_reference', $data['Payment Reference'])
                    ->where('product_sku', $product['sku'])
                    ->exists()) {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => $data['Order ID'],
                        'reason' => 'Product already exists for this payment reference (SKU: '.$product['sku'].')',
                        'email' => $data['Email'] ?? 'N/A',
                    ];

                    continue;
                }

                try {
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
                        'product_id' => Product::findOrCreateByName($product['name'])->id,
                        'product_sku' => $product['sku'],
                        'product_price' => (float) $product['price'],
                        'quantity' => (int) $product['quantity'],
                        'size' => $product['size'],
                        'notes' => $data['Private Notes'] ?? null,
                        'paid_at' => $data['Paid at'] ? Carbon::parse($data['Paid at']) : null,
                        'confirmed_at' => now(),
                        'created_at' => Carbon::parse($data['Created at']),
                        'merchant' => $merchant,
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
        }
    }

    protected function groupOrderRows(array $headers, array $allRows): array
    {
        $groupedOrders = [];

        foreach ($allRows as $row) {
            $data = array_combine($headers, $row);
            $orderId = $data['Order ID'];

            // Check if this is a continuation row (same Order ID, empty email)
            $isContinuationRow = isset($groupedOrders[$orderId]) && empty($data['Email']);

            if ($isContinuationRow) {
                // Add product data to existing order
                $groupedOrders[$orderId]['products'][] = [
                    'name' => $data['Lineitem name'],
                    'sku' => $data['Lineitem sku'],
                    'price' => $data['Lineitem price'],
                    'quantity' => $data['Lineitem quantity'],
                    'size' => $data['Lineitem variant'] ?? null,
                ];
            } else {
                // Create new order or update existing one with main order data
                $groupedOrders[$orderId] = [
                    'main_data' => $data,
                    'products' => [[
                        'name' => $data['Lineitem name'],
                        'sku' => $data['Lineitem sku'],
                        'price' => $data['Lineitem price'],
                        'quantity' => $data['Lineitem quantity'],
                        'size' => $data['Lineitem variant'] ?? null,
                    ]],
                ];
            }
        }

        return $groupedOrders;
    }

    protected function processTwintFile(string $path, string $merchant): void
    {
        $content = file_get_contents($path);

        // Convert from Windows-1252 to UTF-8 if needed
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $lines = explode("\n", $content);

        // Get the highest existing TW order number to continue from
        $lastOrder = Order::where('order_id', 'like', 'TW%')
            ->orderByRaw('CAST(SUBSTRING(order_id, 3) AS UNSIGNED) DESC')
            ->first();
        $orderCounter = $lastOrder ? (int) substr($lastOrder->order_id, 2) + 1 : 1;

        // Skip header lines and find data start
        $dataStartIndex = 0;
        $headerLine = '';
        foreach ($lines as $index => $line) {
            if (preg_match('/^"20\d{2}\./', $line) && str_contains($line, '"Zahlung"')) {
                $dataStartIndex = $index;
                break;
            }
            // Look for header line to understand column structure
            if (str_contains($line, 'Status') && str_contains($line, 'Typ')) {
                $headerLine = $line;
            }
        }

        // Parse header to find correct column indices
        $statusIndex = 20; // default
        $typeIndex = 2;    // default
        if (! empty($headerLine)) {
            $headers = str_getcsv($headerLine, ';');
            foreach ($headers as $index => $header) {
                $trimmedHeader = trim($header, '"');
                if ($trimmedHeader === 'Status') {
                    $statusIndex = $index;
                }
                if ($trimmedHeader === 'Typ') {
                    $typeIndex = $index;
                }
            }
            // Log the detected indices for debugging
            Log::info('TWINT CSV column indices detected', [
                'statusIndex' => $statusIndex,
                'typeIndex' => $typeIndex,
                'headerLine' => $headerLine,
            ]);
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

                // Skip rows with status 'Fehlgeschlagen' using dynamic column index
                $status = $data[$statusIndex] ?? '';
                if (trim($status) == 'Fehlgeschlagen') {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                        'reason' => 'Transaction failed (Status: Fehlgeschlagen)',
                        'email' => $data[18] ?? 'N/A',
                    ];
                    $orderCounter++;

                    continue;
                }

                // Skip rows with Typ that is not 'Zahlung' using dynamic column index
                $type = $data[$typeIndex] ?? '';
                if (trim($type) != 'Zahlung') {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                        'reason' => 'Not a payment transaction (Typ: '.trim($type).')',
                        'email' => $data[18] ?? 'N/A',
                    ];
                    $orderCounter++;

                    continue;
                }

                $orderId = 'TW'.str_pad($orderCounter, 5, '0', STR_PAD_LEFT);

                // Skip if order already exists (check by payment_reference)
                if (Order::where('payment_reference', $data[9])->exists()) {
                    $this->skipped++;
                    $this->skippedRows[] = [
                        'order_id' => $orderId,
                        'reason' => 'Order already exists (payment reference match)',
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
                    'product_id' => Product::findOrCreateByName($data[20] ?? '')->id,
                    'quantity' => 1,
                    'paid_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0].' '.$data[1]),
                    'created_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0].' '.$data[1]),
                    'merchant' => $merchant,
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
