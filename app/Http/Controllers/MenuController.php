<?php

namespace App\Http\Controllers;

use App\DTOs\MenuDTO;
use App\Http\Requests\MenuStoreRequest;
use App\Http\Requests\MenuUpdateRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MenuController extends BaseController
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(): View
    {
        return view('admin.menu.index');
    }

    public function getAll(Request $request): JsonResponse
    {

        // 1. Authorize the action using the MenuPolicy
        // This will throw an AuthorizationException (403 Forbidden) if the user is not authorized
        // to view any categories (i.e., viewAny returns false).
        $this->authorize('viewAny', Menu::class);

        // 2. Get the authenticated user.
        $user = Auth::user();

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
            $data               = $this->menuService->getPaginated($user, $perPage, $search);
            $response           = MenuResource::collection($data)->response()->getData(true);
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

    public function create(): View
    {
        $this->authorize('create', Menu::class);

        $menu        = new Menu();
        $permissions = $this->menuService->getAllPermissions();
        $parentMenus = $this->menuService->parentOptions();

        return view('admin.menu.create', compact('parentMenus', 'permissions', 'menu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuStoreRequest $request)
    {

        try {
            $this->authorize('create', Menu::class);

            // Log raw incoming data
            $this->logInfo('Raw data for new Menu', $request->all());

            // Validate and prepare DTO
            $dto = $this->createDTOFromRequest($request);

            $data  = $this->menuService->createMenu($dto);

            return $this->successResponse(new MenuResource($data), 'Menu Succesfully Created', 201, ['redirect' => route('menu.index')]);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        }
    }

    public function edit(Menu $menu): View
    {
        $this->authorize('update', $menu);

        $parentMenus = $this->menuService->parentOptions();
        $permissions = $this->menuService->getAllPermissions();
        return view('admin.menu.create', compact('menu', 'parentMenus', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(MenuUpdateRequest $request, Menu $menu)
    {

        try {
            $this->authorize('update', $menu);

            // Log raw incoming data
            $this->logInfo('Raw data for update Menu', $request->all());

            // Validate and prepare DTO
            $dto = $this->createDTOFromRequest($request);

            $data  = $this->menuService->update($menu, $dto);

            return $this->successResponse(new MenuResource($data), 'Menu Succesfully Updated', 200, ['redirect' => route('menu.index')]);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Menu $menu)
    {
        try {
            $this->authorize('delete', $menu);

            // Log raw incoming data
            $this->logInfo('Raw request data for delete Menu');

            $delete = $this->menuService->delete($menu->id);

            if ($delete) {
                return $this->successResponse(NULL, 'Menu Deleted Successfully');
            } else {
                return $this->errorResponse('Some Child Record exist with this Menu', 409);
            }
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        }
    }

    private function createDTOFromRequest($data): MenuDTO
    {
        $validatedData = $data->validated();
        // Assuming the user is authenticated and has an ID
        $validatedData['created_by'] = $data->user()->id ?? null;

        $this->logInfo('Passing Request to DTO', $validatedData);

        $dto = MenuDTO::fromArray($validatedData);

        $this->logInfo('DTO created:', $dto->toArray());

        return $dto;
    }
}
