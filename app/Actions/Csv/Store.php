<?php

namespace App\Actions\Csv;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Store
{
    protected string $storagePath = 'csv/pending';

    public function execute(Request $request): array
    {
        return $this->processFiles($request->file('files'));
    }

    protected function processFiles($files): array
    {
        if (! is_array($files)) {
            $files = [$files];
        }

        return array_values(array_filter(array_map(fn ($file) => $this->validateAndProcessFile($file), $files)));
    }

    protected function validateAndProcessFile($file): ?array
    {
        return $file->isValid() ? $this->processFile($file) : null;
    }

    protected function processFile($file): array
    {
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = $this->sanitizeFilename($baseName);
        $uniqueId = uniqid();
        $extension = $file->getClientOriginalExtension();

        $filename = $this->generateFilename($cleanName, $uniqueId, $extension);
        $path = $this->storeFile($file, $filename);

        return $this->getFileDetails($file, $filename, Storage::url($path), $path);
    }

    protected function generateFilename(string $cleanName, string $uniqueId, string $extension): string
    {
        return "{$cleanName}_{$uniqueId}.{$extension}";
    }

    protected function storeFile($file, string $filename): string
    {
        return $file->storeAs($this->storagePath, $filename, 'public');
    }

    protected function getFileDetails($file, string $filename, string $url, string $path): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'name' => $filename,
            'url' => $url,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'pending',
        ];
    }

    protected function sanitizeFilename(string $filename): string
    {
        return Str::slug($filename, '_');
    }
}
