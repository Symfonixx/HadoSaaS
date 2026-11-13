<?php

namespace Modules\Cms\Repositories\Page;

use Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;
use Modules\Cms\Models\Page;
use Modules\Core\Traits\FileTrait;

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
        $ids = $this->normalizeDeletionIds($ids);

        $queuedPathHashes = DB::transaction(function () use ($ids) {
            $pages = Page::query()->whereIn('id', $ids)->lockForUpdate()->get();
            if ($pages->count() !== count($ids)) {
                $missing = array_values(array_diff($ids, $pages->modelKeys()));
                throw (new ModelNotFoundException)->setModel(Page::class, $missing);
            }

            $mediaPaths = $pages->flatMap(fn (Page $page) => [$page->image, $page->meta_image])
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->unique()
                ->values();

            if (Page::destroy($ids) !== count($ids)) {
                throw new \RuntimeException('The selected pages could not all be deleted.');
            }

            $mediaPaths->each(fn (string $path) => $this->queueFileDeletionIfUnreferenced($path));

            return $mediaPaths->map(fn (string $path) => hash('sha256', $path))->all();
        }, 3);

        try {
            $this->processPendingFileDeletions();

            return $queuedPathHashes === [] || ! DB::table('pending_file_deletions')
                ->where('disk', 'public')
                ->whereIn('path_hash', $queuedPathHashes)
                ->exists();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
