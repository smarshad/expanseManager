<?php

namespace App\Services;

use App\DTOs\BackupRequestDTO;
use App\Interfaces\BackupRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseBackupService
{
    public function __construct(
        protected BackupRepositoryInterface $repository
    ) {}

    /**
     * Generates a database backup using chunked native PHP logic.
     * Designed for larger databases to avoid memory exhaustion.
     */
    public function generate(BackupRequestDTO $dto): string
    {
        // Increase execution time for large backups
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $filename = "backup_" . now()->format('Ymd_His') . ".sql.gz";
        $path = $this->repository->path($filename);
        
        // Open a temporary file for writing the SQL
        $tempFile = storage_path('app/temp_backup_' . uniqid() . '.sql');
        $handle = fopen($tempFile, 'w');

        fwrite($handle, "-- Laravel Chunked Native Backup\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($dto->tables as $table) {
            // 1. Get Table Structure
            $createTable = DB::select("SHOW CREATE TABLE `$table`")[0];
            $createTableArray = (array)$createTable;
            fwrite($handle, "-- Structure for table: $table\n");
            fwrite($handle, $createTableArray['Create Table'] . ";\n\n");

            // 2. Get Table Data using Chunks to save memory
            $query = DB::table($table);
            
            if ($dto->from) {
                $query->where('created_at', '>=', $dto->from . ' 00:00:00');
            }
            if ($dto->to) {
                $query->where('created_at', '<=', $dto->to . ' 23:59:59');
            }
            if ($dto->createdBy) {
                $query->where('created_by', $dto->createdBy);
            }

            fwrite($handle, "-- Data for table: $table\n");

            // Process in chunks of 1000 rows
            $query->orderBy(DB::raw('1'))->chunk(1000, function ($rows) use ($handle, $table) {
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $columns = array_keys($rowArray);
                    $values = array_map(function($value) {
                        if ($value === null) return 'NULL';
                        return DB::getPdo()->quote($value);
                    }, array_values($rowArray));

                    fwrite($handle, "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n");
                }
            });
            
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        // 3. Compress the temporary file into the final destination
        $this->compressFile($tempFile, $path);
        
        // Clean up temp file
        unlink($tempFile);

        $this->repository->store($filename, $dto->tables, $dto->requestedBy);

        return $filename;
    }

    /**
     * Compresses a file to .gz format in a memory-efficient way.
     */
    protected function compressFile(string $source, string $destination): void
    {
        $fp_out = gzopen($destination, 'wb9');
        $fp_in = fopen($source, 'rb');

        while (!feof($fp_in)) {
            gzwrite($fp_out, fread($fp_in, 1024 * 512)); // 512KB chunks
        }

        fclose($fp_in);
        gzclose($fp_out);
    }
}
