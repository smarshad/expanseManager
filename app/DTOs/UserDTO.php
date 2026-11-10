<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $created_by,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password,
        public readonly ?string $mobile_no,
        public readonly array $roles, // Array of role IDs or names
        public readonly array $permissions = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            mobile_no: $data['mobile_no'] ?? null,
            created_by: $data['created_by'] ?? Auth::id(),
            roles: $data['roles'] ?? [], // Can be IDs or names
            permissions: $data['permissions'] ?? []
        );
    }
    
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            password: null, // never expose password!
            mobile_no: $user->mobile_no,
            created_by: $user->created_by,
            roles: $user->roles->pluck('name')->toArray(), // Changed: Get role NAMES
            permissions: $user->permissions->pluck('name')->toArray()
        );
    }
    
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'created_by' => $this->created_by,
            'mobile_no' => $this->mobile_no,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        return $data;
    }

    /**
     * Get role names - converts IDs to names if needed
     */
    public function getRoles(): array
    {
        return $this->resolveToRoleNames($this->roles);
    }

    /**
     * Get permission names - converts IDs to names if needed
     */
    public function getPermissions(): array
    {
        return $this->resolveToPermissionNames($this->permissions);
    }

    /**
     * Convert role identifiers to role names
     */
    private function resolveToRoleNames(array $roles): array
    {
        $roleNames = [];
        
        foreach ($roles as $role) {
            if (is_numeric($role)) {
                // Find role by ID
                $roleModel = Role::find($role);
                if ($roleModel) {
                    $roleNames[] = $roleModel->name;
                } else {
                    // Log or handle missing role
                    \Log::warning("Role ID {$role} not found");
                }
            } else {
                // Already a name
                $roleNames[] = $role;
            }
        }
        
        return array_unique($roleNames);
    }

    /**
     * Convert permission identifiers to permission names
     */
    private function resolveToPermissionNames(array $permissions): array
    {
        $permissionNames = [];
        
        foreach ($permissions as $permission) {
            if (is_numeric($permission)) {
                // Find permission by ID
                $permissionModel = Permission::find($permission);
                if ($permissionModel) {
                    $permissionNames[] = $permissionModel->name;
                } else {
                    \Log::warning("Permission ID {$permission} not found");
                }
            } else {
                // Already a name
                $permissionNames[] = $permission;
            }
        }
        
        return array_unique($permissionNames);
    }

    /**
     * Validate that all roles exist
     */
    public function validateRoles(): bool
    {
        $roleNames = $this->getRoles();
        
        foreach ($roleNames as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                throw new \DomainException("Role '{$roleName}' does not exist.");
            }
        }
        
        return true;
    }

    /**
     * Validate that all permissions exist
     */
    public function validatePermissions(): bool
    {
        $permissionNames = $this->getPermissions();
        
        foreach ($permissionNames as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                throw new \DomainException("Permission '{$permissionName}' does not exist.");
            }
        }
        
        return true;
    }

    /**
     * Get the raw roles (IDs or names as provided)
     */
    public function getRawRoles(): array
    {
        return $this->roles;
    }

    /**
     * Get the raw permissions (IDs or names as provided)
     */
    public function getRawPermissions(): array
    {
        return $this->permissions;
    }
}