<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\Store;
use App\Http\Requests\Category\Update;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use App\DTOs\CategoryDTO;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use DomainException;

class CategoryController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(protected CategoryService $categoryService) {}

    public function list(Request $request): JsonResponse
    {
        // 1. Authorize the action using the CategoryPolicy
        // This will throw an AuthorizationException (403 Forbidden) if the user is not authorized
        // to view any categories (i.e., viewAny returns false).
        $this->authorize('viewAny', Category::class);

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
        $categories         = $this->categoryService->getCategoriesPaginated($user, $perPage, $search);
        $response           = CategoryResource::collection($categories)->response()->getData(true);
        $response['draw']   = (int) $draw;
        $response['recordsTotal'] = $response['meta']['total'];
        $response['recordsFiltered'] = $response['meta']['total'];
        return response()->json($response);
        // Using collection::make for paginated resource response
        // return CategoryResource::collection($categories);
    }

    public function index()
    {
        return view('admin.category.index');
    }

    public function classification()
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::with('parent.parent')
            ->withCount('expenses')
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (Category $c) => strtolower(($c->parent?->name ?? $c->name) . '|' . ($c->parent_id ? '1' : '0') . '|' . $c->name))
            ->values();

        $classifier = app(\App\Services\ExpenseClassificationService::class);
        $rows = $categories->map(function (Category $c) use ($classifier) {
            return [
                'category' => $c,
                'effective' => $classifier->classify($c),
                'inherited' => $classifier->classify($c->parent),
            ];
        });

        return view('admin.category.classification', ['rows' => $rows]);
    }

    public function updateClassification(Request $request, Category $category, \App\Services\ExpenseClassificationService $classifier): JsonResponse
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'transaction_kind' => ['nullable', 'in:' . implode(',', $classifier::KINDS)],
            'spending_nature' => ['nullable', 'in:' . implode(',', $classifier::NATURES)],
            'recurrence' => ['nullable', 'in:' . implode(',', $classifier::RECURRENCES)],
        ]);

        // Only touch the fields that were sent; empty string / null = inherit.
        $category->update(array_map(fn ($v) => $v === '' ? null : $v, $data));
        $category->load('parent.parent');

        return response()->json([
            'effective' => $classifier->classify($category),
            'inherited' => $classifier->classify($category->parent),
        ]);
    }

    public function create()
    {
        $parentCategories = Category::parents()->active()->orderBy('name')->get();
        return view('admin.category._form', ['category' => null, 'parentCategories' => $parentCategories]);
    }

    public function store(Store $request)
    {
        // 1. Authorization
        // Throws AuthorizationException on failure.
        // Laravel's handler will catch it and use the message from your CategoryPolicy.
        $this->authorize('create', Category::class);

        try {
            // 2. Validation & Data Preparation
            // The 'Store' FormRequest already handled validation. If it failed,
            // it would have thrown a ValidationException which we catch below.
            $validatedData = $request->validated();
            $validatedData['created_by'] = $request->user()->id;
            logAction('Passing category data to DTO', 'info', $validatedData);

            // 3. DTO and Service Layer
            $dto = CategoryDTO::fromArray($validatedData);
            $category = $this->categoryService->createCategory($dto);
            logAction('Category created successfully', 'info', ['id' => $category->id]);

            // 4. Success Response
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'callback' => route('category.list'),
                'data' => new CategoryResource($category),
            ], 201);
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
        // The generic `catch (\Exception $e)` and `catch (AuthorizationException $e)`
        // have been removed. They will be handled by app/Exceptions/Handler.php.
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);
        $parentCategories = Category::parents()->active()->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.category._form', compact('category', 'parentCategories'));
    }


    public function update(Update $request, Category $category)
    {
        try {

            $this->authorize('update', $category);

            // Log raw incoming data
            logAction('Raw request data for update Category', 'info', $request->all());

            // Validate
            $validatedData = $request->validated();
            $validatedData['created_by'] = $request->user()->id;
            logAction('Passing update category data to DTO', 'info', $validatedData);

            // DTO creation
            $dto = CategoryDTO::fromArray($validatedData);
            logAction('DTO updated category Data created:', 'info', $dto->toArray());

            // Service layer creation
            $category = $this->categoryService->updateCategory($category->id, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'callback' => route('category.list'),
                'data' => new CategoryResource($category),
            ], 201);
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

    public function destroy(Category $category)
    {
        try {
            $this->authorize('delete', $category);

            logAction('Raw request data for delete Category', 'info', [
                'id' => $category->id
            ]);

            $deleted = $this->categoryService->deleteCategory($category->id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted successfully.',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Some records exist with this category.',
            ], 409);
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