<?php

namespace App\Actions\Database;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Exception;

class Backup
{
    public function execute(): array
    {
        try {
            // Generate filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "database_backup_{$timestamp}";
            $sqlFile = "{$filename}.sql";
            $tarFile = "{$filename}.tar.gz";
            
            // Create backup directory if it doesn't exist
            $this->ensureBackupDirectory();
            
            // Get database configuration
            $config = $this->getDatabaseConfig();
            
            // Create SQL dump
            $sqlPath = $this->createSqlDump($config, $sqlFile);
            
            // Create tar.gz archive
            $archivePath = $this->createTarGzArchive($sqlPath, $tarFile);
            
            // Clean up temporary SQL file
            $this->cleanupTempFile($sqlPath);
            
            return [
                'success' => true,
                'filename' => $tarFile,
                'download_url' => Storage::url("public/backups/{$tarFile}"),
                'file_size' => $this->getFileSize($archivePath),
                'message' => 'Database backup erfolgreich erstellt'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup fehlgeschlagen: ' . $e->getMessage()
            ];
        }
    }
    
    private function ensureBackupDirectory(): void
    {
        if (!Storage::exists('public/backups')) {
            Storage::makeDirectory('public/backups');
            
            // Set proper permissions
            $fullPath = storage_path('app/public/backups');
            if (is_dir($fullPath)) {
                chmod($fullPath, 0755);
            }
        }
    }
    
    private function getDatabaseConfig(): array
    {
        return [
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'socket' => config('database.connections.mysql.unix_socket')
        ];
    }
    
    private function createSqlDump(array $config, string $filename): string
    {
        $tempPath = storage_path("app/temp_{$filename}");
        
        // Build mysqldump command
        $command = [
            'mysqldump',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-autocommit',
            '--lock-tables=false'
        ];
        
        // Add connection parameters
        if ($config['socket']) {
            $command[] = "--socket={$config['socket']}";
        } else {
            $command[] = "--host={$config['host']}";
            $command[] = "--port={$config['port']}";
        }
        
        $command[] = "--user={$config['username']}";
        $command[] = "--password={$config['password']}";
        $command[] = $config['database'];
        
        // Execute mysqldump
        $result = Process::run(implode(' ', array_map('escapeshellarg', $command)) . " > " . escapeshellarg($tempPath));
        
        if (!$result->successful()) {
            throw new Exception("mysqldump failed: " . $result->errorOutput());
        }
        
        if (!file_exists($tempPath)) {
            throw new Exception("SQL dump file was not created");
        }
        
        return $tempPath;
    }
    
    private function createTarGzArchive(string $sqlPath, string $tarFilename): string
    {
        $archivePath = storage_path("app/public/backups/{$tarFilename}");
        $sqlFilename = basename($sqlPath);
        
        // Change to temp directory for cleaner archive structure
        $tempDir = dirname($sqlPath);
        
        $command = [
            'tar',
            '-czf',
            $archivePath,
            '-C',
            $tempDir,
            $sqlFilename
        ];
        
        $result = Process::run(implode(' ', array_map('escapeshellarg', $command)));
        
        if (!$result->successful()) {
            throw new Exception("tar compression failed: " . $result->errorOutput());
        }
        
        if (!file_exists($archivePath)) {
            throw new Exception("Archive file was not created");
        }
        
        return $archivePath;
    }
    
    private function cleanupTempFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    private function getFileSize(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return 'Unknown';
        }
        
        $bytes = filesize($filePath);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}