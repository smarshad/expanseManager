<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DatabaseBackupRequest;
use App\Services\DatabaseBackupService;
use App\DTOs\BackupRequestDTO;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupController extends BaseController
{

    public function index()
    {
        $this->authorize('create', \App\Models\DatabaseBackup::class);

        $tables = \DB::select('SHOW TABLES');
        $tables = array_map(fn($t) => array_values((array) $t)[0], $tables);

        return view('admin.database-backup', compact('tables'));
    }

    public function store(
        DatabaseBackupRequest $request,
        DatabaseBackupService $service
    ) {
        $dto = BackupRequestDTO::fromArray(
            $request->validated(),
            $request->user()->id
        );

        $filename = $service->generate($dto);

        return response()->json([
            'download_url' => route('db.backup.download', $filename)
        ]);
    }

    public function download(string $file)
    {
        $this->authorize('download', \App\Models\DatabaseBackup::class);

        $filename = "backup_" . now()->format('Ymd_His') . ".sql.gz";
        $path = Storage::path("backups/{$filename}");

        $backupDir = dirname($path);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }


        $path = "backups/{$file}"; // ✅ THIS IS THE KEY FIX

        abort_unless(Storage::exists($path), 404);

        return Storage::download($path);
    }
}
