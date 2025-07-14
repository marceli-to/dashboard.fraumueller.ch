<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function exportOrdersCsv(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|exists:orders,id',
        ]);

        // Get the orders with product relationship
        $orders = Order::with('product')
            ->whereIn('id', $validated['order_ids'])
            ->get();

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

        // Create CSV content
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

        // Generate filename with timestamp
        $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Create CSV content
        $output = fopen('php://temp', 'w');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Ensure the directory exists
        Storage::makeDirectory('public/csv/export');

        // Save the CSV file
        $filePath = 'public/csv/export/' . $filename;
        Storage::put($filePath, $csvContent);

        // Return JSON response with filename and download URL
        return response()->json([
            'success' => true,
            'filename' => $filename,
            'download_url' => Storage::url($filePath),
            'message' => 'CSV Datei erfolgreich erstellt'
        ]);
    }
}