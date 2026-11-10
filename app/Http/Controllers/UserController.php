<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{

    public function __construct(private UserService $service) {}


    public function list(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $search = $request->get('search');
        $users = $this->service->paginateForUser(
            Auth::user(),
            (int) $request->get('per_page', 10),
            $search
        );

        return UserResource::collection($users)
            ->additional([
                'draw' => (int) $request->get('draw', 1),
                'recordsTotal' => $users->total(),
                'recordsFiltered' => $users->total(),
            ])
            ->response();
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreUserRequest $request): JsonResponse
    {
        // Debug: Log what's coming in
        logAction('Raw request data', 'info', $request->all());

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            logAction('Passing to DTO', 'info', $validatedData);

            $dto = UserDTO::fromArray($validatedData);
            logAction('DTO created:', 'info', $dto->toArray());

            $user = $this->service->create($dto);

            logAction('user created:', 'info',  [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_by' => $user->created_by,
                'created_at' => $user->created_at,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'redirect' => route('users.index'),
                'data' => new UserResource($user->load(['roles', 'creator'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            logAction('User creation error:', 'warning',  [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('view', $user);
        $user = User::findOrFail($user->id);
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $hasRoles = $user->roles;
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        try {
            $dto = UserDTO::fromArray([
                'id' => $user->id,
                ...$request->validated(),
            ]);

            $updatedUser = $this->service->update($user, $dto);

            logAction('User updated', 'info', [
                'user_id'   => $updatedUser->id,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'User updated successfully',
                'redirect' => route('users.index'),
                'data'     => new UserResource(
                    $updatedUser->load(['roles', 'creator'])
                ),
            ], 200);
        } catch (\Throwable $e) {

            logAction('User update failed', 'error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);

            $this->service->delete($user);

            return response()->json([
                'success'  => true,
                'message'  => 'User deleted successfully.',
                'redirect' => route('users.index'),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success'  => false,
                'message'  => 'User not found.',
                'redirect' => route('users.index'),
            ], 404);
        }
    }
}
