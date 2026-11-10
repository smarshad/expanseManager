<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\PermissionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * PermissionService handles the business logic for Permission CRUD operations.
 * Ab yeh Spatie ke 'guard_name' field ko bhi handle karta hai.
 */
class PermissionService
{

    public function __construct(
        protected PermissionRepositoryInterface $permission_repository_interface
    ) {}
    /**
     * Retrieve all Permissions.
     *
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(): Collection
    {
        // Simple retrieval, future mein pagination ya filtering yahan add ho sakta hai.
        return Permission::all();
    }

    public function getPaginated(?int $perPage = null, ?string $search = null): LengthAwarePaginator
    {
        return $this->permission_repository_interface->getPaginated($perPage, ['*'], $search);
    }

    /**
     * Find a Permission by its ID.
     *
     * @param int $id
     * @return Permission|null
     */
    public function findPermission(int $id): ? Permission
    {
        return Permission::find($id);
    }

    /**
     * Create a new Permission.
     *
     * @param array<string, mixed> $data
     * @return Permission
     */
    public function createPermission(array $data): Permission
    {
        // Validation request layer mein ho chuki hai.
        // Agar 'guard_name' nahi bheja gaya hai, toh hum default 'web' set kar sakte hain.
        // Lekin validation mein required hai, toh yeh hamesha available hoga.
        return Permission::create($data);
    }

    /**
     * Update an existing Permission.
     *
     * @param Permission $Permission
     * @param array<string, mixed> $data
     * @return Permission
     */
    public function updatePermission(Permission $Permission, array $data): Permission
    {
        $Permission->update($data);
        return $Permission;
    }

    /**
     * Delete a Permission.
     *
     * @param Permission $Permission
     * @return bool|null
     */
    public function deletePermission(Permission $Permission): ?bool
    {
        return $Permission->delete();
    }
}