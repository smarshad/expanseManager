<?php

namespace App\Interfaces;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MenuRepositoryInterface
{
    

    /**
     * Create a new menu.
     *
     * @param array $menuDetails
     * @return Menu
     */
    public function createMenu(array $menuDetails): Menu;

    
    /**
     * Get the maximum order value for a given parent.
     *
     * @param int|null $parentId
     * @return int
     */
    public function getMaxOrder(?int $parentId = null): int;

    /**
     * Flattens a hierarchical menu array into a single-level array
     * suitable for an HTML select dropdown.
     *
     * @param array $data The hierarchical menu data.
     * @return array The flattened menu list.
     */
    public function flattenMenu(array $data): array;

    public function getPaginated(?int $perPage = null, array $column = ['*'], ?int $userId = null, ?string $search = null): LengthAwarePaginator;

    /**
     * Update an existing Record
     */

    public function update(Menu $menu, array $data): Menu;

    /**
     * Check if there are any related expense records for a given payment method ID.
     */
    public function hasRelatedChild(int $id): bool;
    
}
