<?php

namespace App\Repositories;

use App\Interfaces\BackupRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Models\DatabaseBackup;

class BackupRepository implements BackupRepositoryInterface
{
    protected string $disk = 'local';


    public function store(string $filename, array $tables, int $requestedBy): void
    {
        DatabaseBackup::create([
            'filename'   => $filename,
            'tables'   => implode(',',$tables),
            'created_by' => $requestedBy,
        ]);
    }


    public function exists(string $filename): bool
    {
        return Storage::disk($this->disk)->exists("backups/{$filename}");
    }

    public function path(string $filename): string
    {
        return Storage::disk($this->disk)->path("backups/{$filename}");
    }
}
