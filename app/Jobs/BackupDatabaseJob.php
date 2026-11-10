<?php

namespace App\Jobs;

use App\DTOs\BackupRequestDTO;
use App\Services\DatabaseBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackupDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     * For 15GB, this needs to be high.
     */
    public $timeout = 3600; // 1 hour

    public function __construct(
        protected BackupRequestDTO $dto
    ) {}

    public function handle(DatabaseBackupService $service)
    {
        try {
            Log::info("Starting large database backup...");
            $filename = $service->generate($this->dto);
            Log::info("Backup completed: $filename");
            
            // Here you could notify the user via email or database notification
            // with the download link.
        } catch (\Exception $e) {
            Log::error("Backup Job Failed: " . $e->getMessage());
            throw $e;
        }
    }
}