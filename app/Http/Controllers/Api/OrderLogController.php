<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderLog;

class OrderLogController extends Controller
{
    public function index()
    {
        $logs = OrderLog::with('order:id,order_id')->orderBy('created_at', 'desc')->get();
        
        return response()->json($logs);
    }
}