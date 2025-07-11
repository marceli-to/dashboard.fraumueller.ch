<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportOrders extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'orders:import';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Import orders from CSV files (creditcard.csv or twint.csv)';

  /**
   * Map product names to standardized versions
   */
  private function standardizeProductName(string $productName): string
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

  /**
   * Execute the console command.
   */
  public function handle()
  {
      $this->info('Order Import Tool');
      $this->info('================');

      // List available CSV files
      $files = collect(Storage::disk('public')->files())
          ->filter(fn($file) => str_ends_with($file, '.csv'))
          ->values()
          ->all();

      if (empty($files)) {
          $this->error('No CSV files found in storage/app/public/');
          return Command::FAILURE;
      }

      $this->info('Available CSV files:');
      foreach ($files as $index => $file) {
          $this->line(($index + 1) . '. ' . $file);
      }

      $choice = $this->ask('Enter the number of the file to import');
      
      if (!is_numeric($choice) || $choice < 1 || $choice > count($files)) {
          $this->error('Invalid choice.');
          return Command::FAILURE;
      }

      $selectedFile = $files[$choice - 1];
      $this->info("Selected: {$selectedFile}");

      if (!$this->confirm('Do you want to proceed with the import?')) {
          $this->info('Import cancelled.');
          return Command::SUCCESS;
      }

      // Determine file type and import
      if (str_contains($selectedFile, 'creditcard')) {
          return $this->importCreditCardFile($selectedFile);
      } elseif (str_contains($selectedFile, 'twint')) {
          return $this->importTwintFile($selectedFile);
      } else {
          $this->error('Unable to determine file type. Please ensure filename contains "creditcard" or "twint".');
          return Command::FAILURE;
      }
  }

  private function importCreditCardFile(string $filename): int
  {
      $this->info('Importing credit card orders...');
      
      $path = Storage::disk('public')->path($filename);
      $handle = fopen($path, 'r');
      
      if (!$handle) {
          $this->error('Could not open file.');
          return Command::FAILURE;
      }

      $headers = fgetcsv($handle);
      $imported = 0;
      $skipped = 0;
      $skippedRows = [];

      $this->withProgressBar(0, function () use ($handle, $headers, &$imported, &$skipped, &$skippedRows) {
        while (($row = fgetcsv($handle)) !== false) {
          try {
            $data = array_combine($headers, $row);
            
            // Skip if order already exists
            if (Order::where('order_id', $data['Order ID'])->exists()) {
                $skipped++;
                $skippedRows[] = [
                    'order_id' => $data['Order ID'],
                    'reason' => 'Order already exists',
                    'email' => $data['Email'] ?? 'N/A'
                ];
                continue;
            }

            $order_id = $data['Order ID'];

            Order::create([
                'order_id' => $order_id,
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

            $imported++;
          } catch (\Exception $e) {
            $this->error("Error importing row: " . $e->getMessage());
            $skipped++;
            $skippedRows[] = [
                'order_id' => $data['Order ID'] ?? 'Unknown',
                'reason' => 'Import error: ' . $e->getMessage(),
                'email' => $data['Email'] ?? 'N/A'
            ];
          }
        }
      });

      fclose($handle);

      $this->newLine();
      $this->info("Import completed!");
      $this->info("Imported: {$imported} orders");
      $this->info("Skipped: {$skipped} orders");

      // Log skipped rows
      if (!empty($skippedRows)) {
          $this->info("Skipped rows details:");
          foreach ($skippedRows as $skippedRow) {
              $this->line("  - Order ID: {$skippedRow['order_id']}, Email: {$skippedRow['email']}, Reason: {$skippedRow['reason']}");
          }
          
          // Also log to file
          Log::info('Credit Card Import - Skipped orders', [
              'total_skipped' => count($skippedRows),
              'skipped_orders' => $skippedRows
          ]);
      }

      return Command::SUCCESS;
  }

  private function importTwintFile(string $filename): int
  {
      $this->info('Importing TWINT orders...');
      
      $path = Storage::disk('public')->path($filename);
      $content = file_get_contents($path);
      
      // Convert from Windows-1252 to UTF-8 if needed
      if (!mb_check_encoding($content, 'UTF-8')) {
          $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
      }
      
      $lines = explode("\n", $content);
      $imported = 0;
      $skipped = 0;
      $orderCounter = 1;
      $skippedRows = [];

      // Skip header lines and find data start
      $dataStartIndex = 0;
      foreach ($lines as $index => $line) {
          if (str_contains($line, '"2025.') && str_contains($line, '"Zahlung"')) {
              $dataStartIndex = $index;
              break;
          }
      }

      $this->withProgressBar(0, function () use ($lines, $dataStartIndex, &$imported, &$skipped, &$orderCounter, &$skippedRows) {
          for ($i = $dataStartIndex; $i < count($lines); $i++) {
              $line = trim($lines[$i]);
              if (empty($line)) continue;

              try {
                  $data = str_getcsv($line, ';');
                  
                  if (count($data) < 20) {
                      $skipped++;
                      $skippedRows[] = [
                          'order_id' => 'TW' . str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                          'reason' => 'Incomplete row (less than 20 columns)',
                          'email' => 'N/A'
                      ];
                      continue;
                  }

                  $orderId = 'TW' . str_pad($orderCounter, 5, '0', STR_PAD_LEFT);
                  
                  // Skip if order already exists
                  if (Order::where('order_id', $orderId)->exists()) {
                      $skipped++;
                      $skippedRows[] = [
                          'order_id' => $orderId,
                          'reason' => 'Order already exists',
                          'email' => $data[18] ?? 'N/A'
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
                      'billing_name' => trim(($data[13] ?? '') . ' ' . ($data[14] ?? '')),
                      'billing_address_1' => $data[15] ?? '',
                      'billing_city' => $data[17] ?? '',
                      'billing_zip' => $data[16] ?? '',
                      'billing_country' => 'Switzerland',
                      'phone' => $data[19] ?? '',
                      'product_name' => $this->standardizeProductName($data[20] ?? ''),
                      'quantity' => 1,
                      'paid_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0] . ' ' . $data[1]),
                      'created_at' => Carbon::createFromFormat('Y.m.d H:i', $data[0] . ' ' . $data[1]),
                  ]);

                  $imported++;
                  $orderCounter++;
              } catch (\Exception $e) {
                  $this->error("Error importing row {$i}: " . $e->getMessage());
                  $skipped++;
                  $skippedRows[] = [
                      'order_id' => 'TW' . str_pad($orderCounter, 5, '0', STR_PAD_LEFT),
                      'reason' => 'Import error: ' . $e->getMessage(),
                      'email' => $data[18] ?? 'N/A'
                  ];
              }
          }
      });

      $this->newLine();
      $this->info("Import completed!");
      $this->info("Imported: {$imported} orders");
      $this->info("Skipped: {$skipped} orders");

      // Log skipped rows
      if (!empty($skippedRows)) {
          $this->info("Skipped rows details:");
          foreach ($skippedRows as $skippedRow) {
              $this->line("  - Order ID: {$skippedRow['order_id']}, Email: {$skippedRow['email']}, Reason: {$skippedRow['reason']}");
          }
          
          // Also log to file
          Log::info('TWINT Import - Skipped orders', [
              'total_skipped' => count($skippedRows),
              'skipped_orders' => $skippedRows
          ]);
      }

      return Command::SUCCESS;
  }
}
