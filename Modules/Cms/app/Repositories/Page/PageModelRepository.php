<?php

namespace Modules\Cms\Repositories\Page;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Page;
use Modules\Core\Traits\FileTrait;
use Illuminate\Support\Facades\DB;

class PageModelRepository implements PageRepository
{
    use FileTrait;

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
        return Page::create($data);
    }

    /**
     * Update an existing page.
     */
    public function update(array $data, Page $page, bool $updateTranslations = false): mixed
    {
        return $page->update($data);
    }

    /**
     * Delete multiple pages and clean up images.
     */
    public function deleteMulti(array $ids): ?bool
    {
        return DB::transaction(function () use ($ids) {
            $pages = Page::whereIn('id', $ids)->get();
            $deleted = Page::destroy($ids);
            $this->deleteFile($pages->pluck('image')->filter()->values()->all());
            return $deleted === count($ids);
        });
    }
}
