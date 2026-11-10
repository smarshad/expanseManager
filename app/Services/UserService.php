<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {}

    public function paginateForUser(User $authUser, int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $createdBy = null;

        // If user can ONLY view own, restrict the query
        if (
            $authUser->can('users.view.own') &&
            ! $authUser->can('users.view.all')
        ) {
            $createdBy = $authUser->id;
        }

        return $this->users->paginate($perPage, $createdBy, $search);
    }

    public function create(UserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            // Validate roles and permissions exist
            $dto->validateRoles();
            $dto->validatePermissions();

            // Create user
            $user = User::create($dto->toArray());

            // Sync roles using role names
            if ($roles = $dto->getRoles()) {
                Log::info('Syncing roles to user', [
                    'user_id' => $user->id,
                    'roles' => $roles
                ]);
                $user->syncRoles($roles);
            }

            // Sync permissions using permission names
            if ($permissions = $dto->getPermissions()) {
                Log::info('Syncing permissions to user', [
                    'user_id' => $user->id,
                    'permissions' => $permissions
                ]);
                $user->syncPermissions($permissions);
            }

            return $user->load(['roles', 'permissions', 'creator']);
        });
    }

    public function update(User $user, UserDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto) {

            $original = $user->only(['name', 'email', 'mobile_no']);

            $user->update($dto->toArray());

            if ($dto->getRoles() !== null) {
                $user->syncRoles($dto->getRoles());
            }

            if ($dto->getPermissions() !== null) {
                $user->syncPermissions($dto->getPermissions());
            }

            logAction('User update details', 'info', [
                'user_id' => $user->id,
                'before'  => $original,
                'after'   => $user->only(['name', 'email', 'mobile_no']),
            ]);

            return $user->load(['roles', 'permissions']);
        });
    }


    public function delete(User $user): bool
    {
        if ($user->hasRole('Super Admin')) {
            throw new \RuntimeException('Super Admin cannot be deleted');
        }

        $user->syncRoles([]);
        $user->syncPermissions([]);

        return $this->users->delete($user);
    }

    public function assignPermissions(User $user, array $selectedPermissions): User
    {
        logAction('assignPermissions user:', 'info', ['user' => $user->toArray()]);
        $firstPermission = $selectedPermissions[0] ?? NULL;

        if (is_numeric($firstPermission)) {
            // Treat as IDs (string or int)
            $permissionModels = Permission::whereIn('id', $selectedPermissions)->get();
        } else {
            // Treat as names
            $permissionModels = Permission::whereIn('name', $selectedPermissions)->get();
        }
        $user->syncPermissions($permissionModels);

        return $user->load(['roles', 'permissions']);
    }
}
