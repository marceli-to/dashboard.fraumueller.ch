<?php
namespace App\Actions\Csv;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class ProcessRenewals
{
  public function execute(string $filePath): array
  {
    if (!Storage::disk('public')->exists($filePath)) {
      throw new \Exception('File not found: ' . $filePath);
    }

    $fullPath = Storage::disk('public')->path($filePath);

    $handle = fopen($fullPath, 'r');
    $header = fgetcsv($handle, 0, ';');

    // Find column indices
    $emailIndex = array_search('Customer Email', $header);
    $createdIndex = array_search('Created date (UTC)', $header);
    $descriptionIndex = array_search('Description', $header);
    if ($emailIndex === false || $createdIndex === false) {
      fclose($handle);
      throw new \Exception('CSV must contain "Customer Email" and "Created date (UTC)" columns.');
    }

    $imported = 0;
    $skipped = 0;
    $skippedRows = [];
    $errors = [];

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
      $email = trim($row[$emailIndex] ?? '');
      $createdAt = trim($row[$createdIndex] ?? '');
      $description = $descriptionIndex !== false ? trim($row[$descriptionIndex] ?? '') : '';

      if (empty($email)) {
        continue;
      }

      // Skip rows with a description (these are website orders, not renewals)
      if (!empty($description)) {
        $skipped++;
        $skippedRows[] = [
          'order_id' => '-',
          'email' => $email,
          'reason' => 'Übersprungen (Webshop-Bestellung)',
        ];
        continue;
      }

      $order = Order::where('email', $email)->first();

      if (!$order) {
        $skipped++;
        $skippedRows[] = [
          'order_id' => '-',
          'email' => $email,
          'reason' => 'Keine Bestellung gefunden',
        ];
        continue;
      }

      if ($order->renewed_at !== null) {
        $skipped++;
        $skippedRows[] = [
          'order_id' => $order->order_id,
          'email' => $email,
          'reason' => 'Bereits erneuert am ' . $order->renewed_at->format('d.m.Y'),
        ];
        continue;
      }

      try {
        $order->update(['renewed_at' => $createdAt]);
        $imported++;
      } catch (\Exception $e) {
        $errors[] = "Fehler bei {$email}: " . $e->getMessage();
      }
    }

    fclose($handle);

    // Move to processed
    $processedPath = 'csv/processed/' . basename($fullPath);
    Storage::disk('public')->move($filePath, $processedPath);

    return [
      'imported' => $imported,
      'skipped' => $skipped,
      'skipped_rows' => $skippedRows,
      'errors' => $errors,
    ];
  }
}
