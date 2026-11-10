<?php

namespace App\Services;

use App\DTOs\MenuDTO;
use App\Interfaces\MenuRepositoryInterface;
use App\Models\Menu;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MenuService
{
    public function __construct(
        protected MenuRepositoryInterface $menuRepository
    ) {}


    public function getPaginated(User $user, ?int $perPage = null, ?string $search = null): LengthAwarePaginator
    {
        $userId = null; // Default: view all
        // Check for 'view all' permission
        if ($user->can('menu.view.all')) {
            $userId = null; // No filtering needed
        } elseif ($user->can('menu.view.own')) {
            // If only 'view own' is granted, filter by the user's ID
            $userId = $user->id;
        }
        return $this->menuRepository->getPaginated($perPage, ['*'], $userId, $search);
    }

    public function createMenu(MenuDTO $menuDTO): Menu
    {
        $data = $menuDTO->toArray();

        // If order is not set, calculate the next order value
        if (!isset($data['order'])) {
            $data['order'] = $this->menuRepository->getMaxOrder($data['parent_id']) + 1;
        }

        return $this->menuRepository->createMenu($data);
    }

    /**
     * Get all menus for the parent dropdown.
     *
     * @return Collection
     */
    public function getMenus(): Collection
    {
        return Menu::all(['id', 'name', 'parent_id']);
    }

    public function flattenMenu($data): array // Changed return type from Collection to array
    {
        // 1. Check if $data is a Collection and convert it to a native array.
        // This is the fix for the error on line 47.
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // 2. Pass the now-native array to the Repository.
        return $this->menuRepository->flattenMenu($data);
    }

    /**
     * Get all permissions for the form dropdown (assuming Spatie's Permission model).
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        // We need to assume the Permission model exists and is accessible
        // In a real application, you would inject or resolve this dependency
        try {
            return \Spatie\Permission\Models\Permission::all(['id', 'name', 'module']);
        } catch (\Throwable $e) {
            // Fallback for demonstration if Spatie is not installed
            return collect([]);
        }
    }


    public function parentOptions(?int $excludeId = null): Collection
    {
        $menus = Menu::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('order')
            ->get();

        return $this->flattenMenus($menus, 0, $excludeId);
    }

    private function flattenMenus($menus, int $level = 0, ?int $excludeId = null)
    {
        $result = collect();
        foreach ($menus as $menu) {
            if ($menu->id === $excludeId) {
                continue;
            }

            $indent = str_repeat('—   ', $level);
            $arrow  = $level > 0 ? '----> ' : '';

            $result->push([
                'id'   => $menu->id,
                'name' => $indent . $arrow . $menu->name,
            ]);

            // ✅ USE childrenRecursive (NOT children)
            if ($menu->childrenRecursive->isNotEmpty()) {
                $result = $result->merge(
                    $this->flattenMenus(
                        $menu->childrenRecursive,
                        $level + 1,
                        $excludeId
                    )
                );
            }
        }

        return $result;
    }

    public function sidebarMenus(User $user): Collection
    {
        $menus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['childrenRecursive.permission'])
            ->orderBy('order')
            ->get();

        return $this->applyPermissionFilter($menus, $user);
    }

    /**
     * 🔥 Filter menus bottom-up (children → parent)
     */
    private function applyPermissionFilter(Collection $menus, User $user): Collection
    {
        return $menus->map(function (Menu $menu) use ($user) {

            // 1️⃣ FIRST: filter children recursively
            $filteredChildren = collect();

            if ($menu->childrenRecursive->isNotEmpty()) {
                $filteredChildren = $this->applyPermissionFilter(
                    $menu->childrenRecursive,
                    $user
                );
            }

            // Replace children with filtered result
            $menu->setRelation('childrenRecursive', $filteredChildren);

            // 2️⃣ SECOND: decide if THIS menu should appear
            $canViewThisMenu =
                $this->canViewMenu($menu, $user)
                || $filteredChildren->isNotEmpty();

            return $canViewThisMenu ? $menu : null;
        })->filter()->values();
    }


    /**
     * 🔐 Permission check for a single menu
     * Checks by permission name for efficiency.
     */
    private function canViewMenuOldWorking(Menu $menu, User $user): bool
    {
        // Super Admin sees everything
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Leaf menu with no permission → show
        if (!$menu->permission_id && $menu->childrenRecursive->isEmpty()) {
            return true;
        }
        // Menu with permission → check it
        if ($menu->permission_id) {

            $permissionName = $menu->permission->name ?? null;
            if (!$permissionName) {
                return false;
            }

            static $permissionNames = null;

            if ($permissionNames === null) {
                $permissionNames = $user->getAllPermissions()->pluck('name');
            }

            // 🔥 allow *.own OR *.all
            if (str_ends_with($permissionName, '.all')) {
                $ownPermission = str_replace('.all', '.own', $permissionName);

                return $permissionNames->contains($permissionName)
                    || $permissionNames->contains($ownPermission);
            }

            return $permissionNames->contains($permissionName);
        }


        // Parent menu with no permission and no visible children → HIDE
        return false;
    }

    private function canViewMenu(Menu $menu, User $user): bool
    {

        // Debug info
        // logger()->info('Checking menu', [
        //     'menu_id' => $menu->id,
        //     'menu_name' => $menu->name,
        //     'permission_id' => $menu->permission_id,
        //     'permission_name' => $menu->permission->name ?? 'NULL',
        //     'user_permissions' => $user->getPermissionNames()->toArray()
        // ]);
        // Super Admin sees everything
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Menu without permission requirement
        if (!$menu->permission_id) {
            return true;
        }

        // Get the permission
        $permission = $menu->permission;
        if (!$permission) {
            return false;
        }

        $permissionName = $permission->name;

        // User's permissions (cached for performance)
        static $userPermissions = null;
        if ($userPermissions === null) {
            $userPermissions = $user->getAllPermissions()->pluck('name');
        }

        // Check for exact permission
        if ($userPermissions->contains($permissionName)) {
            return true;
        }

        // Handle .all/.own fallback logic
        if (str_ends_with($permissionName, '.all')) {
            $ownPermission = str_replace('.all', '.own', $permissionName);
            return $userPermissions->contains($ownPermission);
        }

        // Also check for the reverse: if user has .all but menu requires .own
        if (str_ends_with($permissionName, '.own')) {
            $allPermission = str_replace('.own', '.all', $permissionName);
            return $userPermissions->contains($allPermission);
        }

        return false;
    }

    public function update(Menu $menu, MenuDTO $dto): Menu
    {
        return $this->menuRepository->update($menu, $dto->toArray());
    }

    public function delete(int $id): bool
    {
        $this->validateMenuCanBeDeleted($id);

        // Perform deletion


        $delete = $this->menuRepository->delete($id);

        if (!$delete) {
            throw new DomainException('The repository failed to delete the Menu.');
        }


        return true;
    }

    /**
     * Business rule: Check if the payment method can be deleted.
     * @throws DomainException
     */
    private function validateMenuCanBeDeleted(int $id): void
    {
        if ($this->menuRepository->hasRelatedChild($id)) {
            // Use DomainException with a specific message for the controller to handle
            throw new DomainException('Cannot deletepayment method. Related expense records exist.');
        }
    }
}
