<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        return view('admin.vendor.index');
    }

    public function list(Request $request): JsonResponse
    {
        $perPage = max(1, (int) $request->integer('per_page', 10));
        $search = trim((string) $request->input('search', ''));

        $vendors = Vendor::query()
            ->with('category:id,name')
            ->where('user_id', $request->user()->id)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($vendors->items())->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'category' => $vendor->category?->name,
                'is_active' => $vendor->is_active,
                'created_at' => $vendor->created_at?->format('Y-m-d H:i:s'),
                'can' => ['update' => true, 'delete' => ! $vendor->expenses()->exists()],
            ]),
            'draw' => (int) $request->integer('draw', 1),
            'recordsTotal' => $vendors->total(),
            'recordsFiltered' => $vendors->total(),
            'meta' => ['total' => $vendors->total()],
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.vendor._form', [
            'vendor' => null,
            'categories' => $this->categories($request),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $vendor = Vendor::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully.',
            'callback' => route('vendor.list'),
            'data' => $vendor,
        ], 201);
    }

    public function edit(Request $request, Vendor $vendor)
    {
        $this->ensureOwner($request, $vendor);

        return view('admin.vendor._form', [
            'vendor' => $vendor,
            'categories' => $this->categories($request),
        ]);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $this->ensureOwner($request, $vendor);
        $vendor->update($this->validated($request, $vendor));

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully.',
            'callback' => route('vendor.list'),
            'data' => $vendor->fresh(),
        ]);
    }

    public function destroy(Request $request, Vendor $vendor): JsonResponse
    {
        $this->ensureOwner($request, $vendor);

        if ($vendor->expenses()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor is used by an expense and cannot be deleted.',
            ], 409);
        }

        $vendor->delete();

        return response()->json(['success' => true, 'message' => 'Vendor deleted successfully.']);
    }

    private function validated(Request $request, ?Vendor $vendor = null): array
    {
        $userId = $request->user()->id;

        return $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('vendors')->where(fn ($query) => $query->where('user_id', $userId))->ignore($vendor?->id),
            ],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('created_by', $userId)],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function ensureOwner(Request $request, Vendor $vendor): void
    {
        abort_unless($vendor->user_id === $request->user()->id, 403, 'You cannot manage this vendor.');
    }

    private function categories(Request $request)
    {
        return Category::query()
            ->where('created_by', $request->user()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
