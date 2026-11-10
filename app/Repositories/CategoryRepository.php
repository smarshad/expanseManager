<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll(): Collection
    {
        return Category::all();
    }

    public function getPaginated(int $perPage = 15, array $columns = ['*'], ?int $userId = null, ?string $search = NULL): LengthAwarePaginator
    {

        $query = Category::query()->with('parent');

        if ($userId !== null) {
            // Filter categories by the user who created them
            $query->where('created_by', $userId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('color_code', 'like', "%{$search}%")
                  ->orWhereHas('parent', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // $sql = vsprintf(
        //     str_replace('?', '%s', $query->toSql()),
        //     collect($query->getBindings())->map(fn($b) => "'$b'")->toArray()
        // );

        // \Log::info($sql);

        // Advanced: We can add complex filtering/sorting logic here
        return $query->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage, $columns);
    }

    public function findById(int $categoryId): ?Category
    {
        // Using findOrFail ensures the Service layer doesn't need to check for null
        return Category::findOrFail($categoryId);
    }

    public function create(array $details): Category
    {
        return Category::create($details);
    }

    public function update(int $categoryId, array $newDetails): Category
    {
        $category = $this->findById($categoryId);
        $category->update($newDetails);
        return $category;
    }

    public function delete(int $categoryId): bool
    {
        return $this->findById($categoryId)->delete();
    }

}
