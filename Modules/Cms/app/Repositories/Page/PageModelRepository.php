<?php

namespace Modules\Cms\Repositories\Page;

use Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Page;

class PageModelRepository implements PageRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Page::select($columns)->latest()
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->when(isset($filters['type']) && $filters['type'] !== null && $filters['type'] !== '',
                fn ($q) => $q->where('type', $filters['type']))
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Page
    {
        return Page::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        $page = new Page($data);

        return $page->save() ? $page : null;
    }

    public function update(array $data, Page $page, bool $updateTranslations = false): mixed
    {
        return $page->update($data);
    }

    /** @param array<int, int> $ids */
    public function findManyForDeletion(array $ids): Collection
    {
        return Page::query()->whereIn('id', $ids)->lockForUpdate()->get();
    }

    /** @param array<int, int> $ids */
    public function deleteMany(array $ids): int
    {
        return Page::destroy($ids);
    }
}
