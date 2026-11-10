<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    
    /**
     * Retrieve records with pagination
     */

    public function getPaginated(?int $perPage = null, array $column = ['*'], ?string $search = NULL): LengthAwarePaginator;
}
