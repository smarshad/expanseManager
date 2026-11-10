<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserPermissionRequest;

class UserPermissionController extends BaseController
{

    public function __construct(private UserService $service) {}

    public function edit(User $user)
    {
        // Optional: Add a policy or gate check to ensure only authorized users can manage permissions
        // Gate::authorize('manage-user-permissions');
        $this->authorize('viewPermission', $user);
        // 1. Fetch all available permissions
        $permissionsGrouped = Permission::getGroupedByModule();

        // 2. Get the user's currently assigned direct permissions
        // We use getDirectPermissions() to exclude permissions inherited via roles
        $userPermissions = $user->permissions()->pluck('id')->toArray();

        $userDirectPermissions = $user->getDirectPermissions()->pluck('id')->toArray();
        $userRolePermissions   = $user->getPermissionsViaRoles()->pluck('id')->toArray();
        // dd($userRolePermissions);
        return view('admin.users.permissions.edit', [
            'user' => $user,
            'permissionsGrouped' => $permissionsGrouped,
            'userPermissions' => $userPermissions,
            'userDirectPermissions' => $userDirectPermissions,
            'userRolePermissions' => $userRolePermissions,
        ]);
    }

    /**
     * Update the user's direct permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */

    public function update(UserPermissionRequest $request, User $user)
    {

        // Debug: Log what's coming in
        logAction('Raw request data for assigning permission to user', 'info', ['request' => $request->all(), 'user id' => $user->id]);
        $this->authorize('editPermission', $user);

        try {

            $validatedData = $request->validated();
            $selectedPermissions = $validatedData['permissions'] ?? [];
            logAction('Passing to DTO', 'info', $validatedData);
            logAction('selectedPermissions', 'info', $selectedPermissions);

            if (empty($selectedPermissions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission is empty',
                    'redirect' => route('users.index'),
                    'error' => ''
                ], 404);
            }


            $user = $this->service->assignPermissions($user, $selectedPermissions);

            return response()->json([
                'success'  => true,
                'message'  => 'Permission successfully assigned to user',
                'redirect' => route('users.index'),
                'data'     => new UserResource($user->load(['roles', 'creator'])),
            ], 201);
        } catch (\Exception $e) {
            logAction('User Assign Permission error:', 'warning',  [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to asign permission to user',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }
}
