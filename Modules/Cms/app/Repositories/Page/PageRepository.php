<?php

namespace Modules\Cms\Repositories\Page;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Page;

interface PageRepository
{
    /**
     * Get all pages with pagination.
     */
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    /**
     * Find a page by its ID.
     */
    public function find(int $id, array $columns = ['*']): ?Page;

    /**
     * Store a new page.
     */
    public function store(array $data): mixed;

    /**
     * Update an existing page.
     */
    public function update(array $data, Page $page, bool $updateTranslations = false): mixed;

    /**
     * Delete multiple pages by their IDs.
     */
    public function deleteMulti(array $ids): ?bool;
}
