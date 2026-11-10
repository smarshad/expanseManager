<?php

namespace App\Repositories;

use App\Interfaces\MenuRepositoryInterface;
use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MenuRepository implements MenuRepositoryInterface
{
    public function __construct(
        protected Menu $model
    ) {}

    /**
     * Create a new menu.
     */
    public function createMenu(array $attributes): Menu
    {
        return $this->model->create($attributes);
    }

    /**
     * Get max order for a given parent.
     */
    public function getMaxOrder(?int $parentId = null): int
    {
        return (int) $this->model
            ->where('parent_id', $parentId)
            ->max('order');
    }

    /**
     * Get paginated menu list with relations.
     */
    public function getPaginated(
        ?int $perPage = null,
        array $columns = ['*'],
        ?int $userId = null,
        ?string $search= NULL
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['parent', 'children', 'permission', 'creator']);
        if ($userId !== null) {
            $query->where('created_by', $userId);
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhereHas('parent', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('permission', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // $sql = vsprintf(
        //     str_replace('?', '%s', $query->toSql()),
        //     collect($query->getBindings())->map(fn($b) => "'$b'")->toArray()
        // );

        // \Log::info($sql);
        // \Log::info("user id $userId");
        return $query->paginate(
            $perPage ?? config('pagination.default'),
            $columns
        );
    }

    /**
     * Flatten menu tree for display.
     */
    public function flattenMenu(array $items): array
    {
        return $this->flattenTree(
            $this->buildTree($items)
        );
    }

    /**
     * Update a menu.
     */
    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);

        return $menu;
    }

    /* -----------------------------------------------------------------
     |  Internal Helpers
     | -----------------------------------------------------------------
     */

    /**
     * Build a nested tree from a flat list.
     */
    private function buildTree(array $items, ?int $parentId = null): array
    {
        $tree = [];
        $indexed = [];

        foreach ($items as $item) {
            $item['children'] = [];
            $indexed[$item['id']] = $item;
        }

        foreach ($indexed as $id => &$item) {
            $pid = $item['parent_id'] ?: null;

            if ($pid !== null && isset($indexed[$pid])) {
                $indexed[$pid]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        return $tree;
    }

    /**
     * Flatten a nested tree with visual hierarchy.
     */
    private function flattenTree(
        array $nodes,
        string $parentPath = '',
        int $level = 0,
        string $separator = '-->'
    ): array {
        $result = [];

        foreach ($nodes as $node) {
            $currentPath = $parentPath
                ? "{$parentPath} {$separator} {$node['name']}"
                : $node['name'];

            $result[] = [
                'id'            => $node['id'],
                'title'         => $node['name'],
                'display_title' => str_repeat($separator . ' ', $level) . $currentPath,
                'level'         => $level,
            ];

            if (!empty($node['children'])) {
                $result = array_merge(
                    $result,
                    $this->flattenTree(
                        $node['children'],
                        $currentPath,
                        $level + 1,
                        $separator
                    )
                );
            }
        }

        return $result;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model
            ->where('id', $id)
            ->delete();
    }

    /**
     * Check if menu has child menus.
     */
    public function hasRelatedChild(int $id): bool
    {
        return $this->model
            ->where('parent_id', $id)
            // ->whereNull('deleted_at')
            ->exists();
    }
}
