<?php

namespace Modules\Cms\Repositories\Page;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Page;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;

class PageModelRepository implements PageRepository
{
    use ExceptionHandlerTrait, FileTrait;

    /**
     * Fetch all pages with optional filters and pagination.
     */
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Page::select($columns)->latest()
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->when(isset($filters['type']) && $filters['type'] !== null && $filters['type'] !== '',
                fn ($q) => $q->where('type', $filters['type']))
            ->paginate(Config::get('core.page_size', 10));
    }

    /**
     * Find a page by ID.
     */
    public function find(int $id, array $columns = ['*']): ?Page
    {
        return Page::find($id, $columns);
    }

    /**
     * Store a new page.
     */
    public function store(array $data): mixed
    {
        return $this->execute(function () use ($data) {
            Page::create($data);
        });
    }

    /**
     * Update an existing page.
     */
    public function update(array $data, Page $page, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $page) {
            $page->update($data);

            return true;
        });
    }

    /**
     * Delete multiple pages and clean up images.
     */
    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            $images = Page::whereIn('id', $ids)->pluck('image')->filter()->toArray();
            Page::destroy($ids);
            $this->deleteFile($images);

            return true;
        });
    }
}
