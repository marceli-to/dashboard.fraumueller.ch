<?php

namespace App\Http\Controllers\Api;

use App\Actions\Csv\Process as ProcessCsvAction;
use App\Actions\Csv\Store as StoreCsvAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUploadRequest;
use Illuminate\Http\Request;

class UploadController extends Controller
{
  public function store(StoreUploadRequest $request)
  {
    $files = (new StoreCsvAction)->execute($request);
    return response()->json(['files' => $files]);
  }

  public function process(Request $request)
  {
    $request->validate([
      'file_path' => 'required|string',
      'merchant' => 'nullable|string|in:twint,squarespace',
    ]);

    try {
      $result = (new ProcessCsvAction)->execute($request->file_path, $request->merchant);

      return response()->json([
        'success' => true,
        'message' => 'CSV processed successfully',
        'data' => $result,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error processing CSV: '.$e->getMessage(),
      ], 422);
    }
  }
}
