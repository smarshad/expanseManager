<?php

namespace App\Interfaces;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    
    /**
     * Retrieve records with pagination
     */

    public function getPaginated(?int $perPage = null, array $column = ['*'], ?string $search = NULL): LengthAwarePaginator;

    public function createRole(array $data): Role;
}
