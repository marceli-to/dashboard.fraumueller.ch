<?php
namespace App\Actions\Csv;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Export
{
    public function execute(array $orderIds): array
    {
      // Get the orders with product relationship
      $orders = Order::with('product')
        ->whereIn('id', $orderIds)
        ->get();

      // Generate CSV content
      $csvContent = $this->generateCsv($orders);

      // Generate filename with timestamp
      $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

      // Save the CSV file
      $filePath = $this->saveCsv($filename, $csvContent);

      return [
        'success' => true,
        'filename' => $filename,
        'download_url' => Storage::url($filePath),
        'message' => 'CSV Datei erfolgreich erstellt'
      ];
    }

    private function generateCsv(Collection $orders): string
    {
      // Define CSV headers
      $headers = [
        'Order ID',
        'Product',
        'Email',
        'Phone',
        'Payment Method',
        'Merchant',
        'Total',
        'Paid At',
        'Order Status',
        'Billing Name',
        'Billing Address 1',
        'Billing Address 2',
        'Billing City',
        'Billing ZIP',
        'Billing Country',
        'Shipping Name',
        'Shipping Address 1',
        'Shipping Address 2',
        'Shipping City',
        'Shipping ZIP',
        'Shipping Province',
        'Shipping Country',
        'Notes'
      ];

      // Create CSV data array
      $csvData = [];
      $csvData[] = $headers;

      foreach ($orders as $order) {
        $csvData[] = [
          $order->order_id,
          $order->product ? $order->product->name : '',
          $order->email,
          $order->phone,
          $order->payment_method,
          $order->merchant,
          $order->total,
          $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
          $order->order_status->value ?? $order->order_status,
          $order->billing_name,
          $order->billing_address_1,
          $order->billing_address_2,
          $order->billing_city,
          $order->billing_zip,
          $order->billing_country,
          $order->shipping_name,
          $order->shipping_address_1,
          $order->shipping_address_2,
          $order->shipping_city,
          $order->shipping_zip,
          $order->shipping_province,
          $order->shipping_country,
          $order->notes
        ];
      }

      // Convert to CSV string
      $output = fopen('php://temp', 'w');
      foreach ($csvData as $row) {
          fputcsv($output, $row);
      }
      rewind($output);
      $csvContent = stream_get_contents($output);
      fclose($output);

      return $csvContent;
    }

  private function saveCsv(string $filename, string $csvContent): string
  {
    // Ensure the directory exists with proper permissions
    if (!Storage::exists('public/csv/export')) {
        Storage::makeDirectory('public/csv/export');
        // Fix permissions after creation
        $fullPath = storage_path('app/public/csv/export');
        chmod($fullPath, 0755);
    }

    // Save the CSV file
    $filePath = 'public/csv/export/' . $filename;
    Storage::put($filePath, $csvContent);

    return $filePath;
  }
}