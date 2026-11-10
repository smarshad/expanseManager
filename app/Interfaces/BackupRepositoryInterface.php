<?php

namespace App\Interfaces;

interface BackupRepositoryInterface
{
    public function store(string $filename,array $tables, int $requestedBy): void;

    public function exists(string $filename): bool;

    public function path(string $filename): string;
}
