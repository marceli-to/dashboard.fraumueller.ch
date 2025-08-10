<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;

class OrderLogController extends Controller
{
  public function index()
  {
    $logs = OrderLog::with('order:id,order_id')
      ->orderByRaw("CASE WHEN status = 'error' THEN 0 ELSE 1 END")
      ->orderBy('updated_at', 'desc')
      ->get();
    return response()->json($logs);
  }
}