<?php

namespace App\Services;

use App\DTOs\RoleDTO;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Permission;
use App\Models\Role;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function getPaginated(?int $perPage = null, ?string $search = NULL): LengthAwarePaginator
    {
        return $this->roleRepository->getPaginated($perPage, ['*'], $search);
    }

    public function createRole(RoleDTO $dto): Role
    {

        return DB::transaction(function () use ($dto) {
            logAction('createRole Service', 'info', $dto->toArray());

            $role = $this->roleRepository->createRole(
                $dto->toArray()
            );

            $permissions = Permission::whereIn('id', $dto->permissions)->get();

            logAction('createRole permissions', 'info', $permissions->pluck('name')->toArray());

            if ($permissions->isEmpty()) {
                throw new DomainException('Invalid permissions supplied.');
            }

            $role->syncPermissions($permissions);

            return $role;
        });
    }
}
