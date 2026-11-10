<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{

    public function __construct(protected Role $model) {}

    public function getPaginated(
        ?int $perPage = null,
        array $columns = ['*'],
        ?string $search = null
    ): LengthAwarePaginator {

        $query = $this->model
            ->with('permissions')
            ->withCount('permissions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('permissions', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(
            $perPage ?? config('pagination.default'),
            $columns
        );
    }

    /**
     * Create a new Role.
     */
    public function createRole(array $data): Role
    {
        logAction('RoleRepository', 'info', $data);
        return $this->model->create($data);
    }
}
