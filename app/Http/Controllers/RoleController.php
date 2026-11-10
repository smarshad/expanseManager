<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\DTOs\RoleDTO;
use DomainException;

class RoleController extends BaseController
{

    public function __construct(
        protected RoleService $roleService
    ) {}
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        return view('admin.roles.index');
    }

    public function getAll(Request $request): JsonResponse
    {
        // 1. Authorize the action using the RolePolicy
        // This will throw an AuthorizationException (403 Forbidden) if the user is not authorized
        // to view any categories (i.e., viewAny returns false).

        $user = Auth::user();

        // Authorization happens AFTER logging
        $this->authorize('viewAny', Role::class);

        // Safety check: If the route is not protected by 'auth' middleware, $user could be null.
        // We ensure the user is an instance of the User model before passing it to the service.
        if (!$user instanceof User) {
            // If the user is not authenticated, we throw an exception.
            // In a real Laravel app, the 'auth' middleware should handle this,
            // but this check adds robustness.
            abort(401, 'Unauthenticated.');
        }

        try {
            $perPage            = $request->get('per_page', NULL);
            $draw               = $request->get('draw', 1);
            $search             = $request->get('search');
            $data               = $this->roleService->getPaginated($perPage, $search);
            $response           = RoleResource::collection($data)->response()->getData(true);
            $response['draw']   = (int) $draw;
            $response['recordsTotal'] = $response['meta']['total'];
            $response['recordsFiltered'] = $response['meta']['total'];
            return response()->json($response);
        } catch (ValidationException $e) {
            // This catch block is technically redundant if using a FormRequest,
            // as the FormRequest handles the redirect/JSON response automatically.
            // However, it's kept here for clarity if you ever validate manually.
            logAction('Validation Error', 'error', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (DomainException $e) {
            // This is a good exception to catch here, as it's a specific
            // business logic failure that the controller should know how to report.
            logAction('Domain Error', 'error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Role::class);
        $permissionsGrouped = Permission::getGroupedByModule();
        return view('admin.roles.create', compact('permissionsGrouped'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create', Role::class);

        $dto = $this->createRoleDTOFromRequest($request);

        $this->roleService->createRole($dto);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
        ], 201);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        // $role = Role::find($id);
        // if (!$role) {
        //     logAction('Role not found for editing', 'warning', [
        //         'role_id' => $id,
        //     ]);

        //     return redirect()
        //         ->route('roles.index')
        //         ->with('error', __('roles.not_found'));
        // }

        $permissionsGrouped = Permission::getGroupedByModule();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissionsGrouped', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {

        $validated = $request->validated();
        try {

            $role = role::find($role->id);

            if (!$role) {
                logAction('Role not found for update', 'warning', [
                    'role_id' => $role->id,
                ]);

                return response()->json(['message' => 'Updated successfully']);
            }
            // Save old values
            $old = $role->only(['name', 'description']);

            DB::beginTransaction();

            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            logAction('Role Updated', 'info', [
                'permission_id'      => $role->id,
                'old_name'           => $old['name'],
                'new_name'           => $role->name,
                'old_description'    => $old['description'],
                'new_description'    => $role->description,
            ]);

            // Sync permissions for the role
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
                // logAction('Role Updated update permission', 'info', ['permission_names' => $permissions->pluck('name')->toArray()]);
            }

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('roles.edit', $role->id),
                    'message' => 'Role updated successfully.',
                ], 200);
            }

            return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logAction('Error On Updating Role', 'warning', [
                'permission_id'      => $role->id,
                'old_name'           => $old['name'],
                'new_name'           => $role->name,
                'old_description'    => $old['description'],
                'new_description'    => $role->description,
                'error'              => $e->getMessage(),
            ]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating role: ' . $e->getMessage(),
                ], 500); // ✅ 500 for server errors
            }
            return redirect()->back()->withInput()->with('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role = Role::find($role->id);

        if (!$role) {
            logAction('Role not found for delete', 'warning', [
                'role_id' => $role->id,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found',
                ], 404);
            }

            return redirect()->route('roles.index')
                ->with('error', 'Role not found');
        }

        // Check if role has users assigned
        if ($role->users()->exists()) {
            logAction('Role deletion attempted but has users', 'warning', [
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It is assigned to one or more users.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Cannot delete role. It is assigned to one or more users.');
        }

        // Check if it's a protected role
        if (in_array($role->name, ['Super Admin'])) {
            logAction('Protected role deletion attempted', 'warning', [
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is a system role and cannot be deleted.',
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'This is a system role and cannot be deleted.');
        }

        $oldName = $role->name;

        logAction('Role Deleted', 'info', [
            'role_id' => $role->id,
            'old_name' => $oldName,
        ]);

        $role->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully',
            ], 200);
        }

        return redirect()->route('roles.index')
            ->with('status', 'Role deleted successfully');
    }

    private function createRoleDTOFromRequest($request): RoleDTO
    {
        $validated = $request->validated();

        logAction('Passing Role Request to DTO', 'info', $validated);

        return RoleDTO::fromArray($validated);
    }
}
