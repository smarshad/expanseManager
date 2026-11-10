<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Interfaces\PermissionRepositoryInterface;
use App\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{

    public function __construct(protected Permission $model) {}

    public function getPaginated(?int $perPage = null, array $columns = ['*'], ?string $search = NULL): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate(
            $perPage ?? config('pagination.default'),
            $columns
        );
    }
}
