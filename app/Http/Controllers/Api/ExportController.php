<?php

namespace App\Http\Controllers\Api;

use App\Actions\Csv\Export as ExportOrdersCsvAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportOrdersCsv(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|exists:orders,id',
        ]);

        $result = (new ExportOrdersCsvAction)->execute($validated['order_ids']);

        return response()->json($result);
    }
}