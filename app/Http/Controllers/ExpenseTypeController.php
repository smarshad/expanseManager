<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseFor\StoreExpenseForRequest;
use App\Http\Requests\ExpenseFor\UpdateExpenseForRequest;
use App\Http\Resources\ExpenseTypeResource;
use App\Services\ExpenseTypeService;
use Illuminate\Http\JsonResponse;
use App\DTOs\ExpenseForDTO;
use Illuminate\Http\Request;
use App\Models\ExpenseFor;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

use DomainException;
use Exception;

class ExpenseTypeController extends BaseController // Extend the new BaseController
{
    public function __construct(protected ExpenseTypeService $expenseTypeService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.expensetype.index');
    }


    public function create()
    {
        return view('admin.expensetype._form', ['expensetype' => null]);
    }


    public function getAll(Request $request): JsonResponse
    {

        // 1. Authorize the action using the CategoryPolicy
        // This will throw an AuthorizationException (403 Forbidden) if the user is not authorized
        // to view any categories (i.e., viewAny returns false).
        $this->authorize('viewAny', ExpenseFor::class);

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

        $perPage            = $request->get('per_page', 5);
        $draw               = $request->get('draw', 1);
        $search             = $request->get('search');
        $categories         = $this->expenseTypeService->getExpenseTypePaginated($user, $perPage, $search);
        $response           = ExpenseTypeResource::collection($categories)->response()->getData(true);
        $response['draw']   = (int) $draw;
        $response['recordsTotal'] = $response['meta']['total'];
        $response['recordsFiltered'] = $response['meta']['total'];
        return response()->json($response);
    }


    /**
     * Store a newly created rrecord.
     */
    public function store(StoreExpenseForRequest $request)
    {
        try {
            $this->authorize('create', ExpenseFor::class);
            // Log raw incoming data
            $this->logInfo('Raw request data for New ExpenseFor', $request->all());

            // Validate and prepare DTO
            $dto = $this->createDTOFromRequest($request);

            // Service layer creation
            $data = $this->expenseTypeService->create($dto);

            // Use common success response helper
            return $this->successResponse(
                new ExpenseTypeResource($data),
                'Expense type created successfully.',
                201,
                ['callback' => route('expensetype.list')]
            );
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (Exception $e) {
            return $this->handleUnexpectedException($e);
        }
    }

    /**
     * Display the specified resource.
     */


    public function edit(ExpenseFor $expenseType)
    {
        $this->authorize('update', $expenseType);
        $this->logInfo('edit', ['expenseType' => $expenseType->id]);

        return view('admin.expensetype._form', ['expensetype' => $expenseType]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseForRequest $request, ExpenseFor $expenseType)
    {
        try {

            $this->authorize('update', $expenseType);
            $this->logInfo('Raw request data for UpdateExpenseForRequest', $request->all());

            $dto = $this->createDTOFromRequest($request);

            $expenseType = $this->expenseTypeService->update($expenseType->id, $dto);

            // Use common success response helper
            return $this->successResponse(
                new ExpenseTypeResource($expenseType),
                'ExpenseFor updated successfully.',
                200,
                ['callback' => route('expensetype.list')]
            );
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        }
    }

    /**
     * Helper to create DTO from request.
     */
    private function createDTOFromRequest(Request $request): ExpenseForDTO
    {
        $validatedData = $request->validated();
        // Assuming the user is authenticated and has an ID
        $validatedData['created_by'] = $request->user()->id ?? null;

        $this->logInfo('Passing Request to DTO', $validatedData);

        $dto = ExpenseForDTO::fromArray($validatedData);

        $this->logInfo('DTO created:', $dto->toArray());

        return $dto;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseFor $expenseType)
    {
        try {
            $this->authorize('delete', $expenseType);

            // Log raw incoming data
            $this->logInfo('Raw request data for delete expenseType', [$expenseType->id]);
            $data = $this->expenseTypeService->delete($expenseType->id);

            if ($data) {
                return $this->successResponse(null, 'expenseType deleted successfully.');
            } else {
                // This is a business logic error, but since it's a simple boolean return,
                // we'll treat it as a failure to delete due to constraints.
                return $this->errorResponse('Some record exist with this expenseType.', 409);
            }
        } catch (ValidationException $e) {

            // Validation-specific errors
            logAction('Validation Error', 'error', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validation Failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (DomainException $e) {

            logAction('Domain Error', 'error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
