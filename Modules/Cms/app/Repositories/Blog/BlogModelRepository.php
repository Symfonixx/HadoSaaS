<?php

namespace Modules\Cms\Repositories\Blog;

use Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Blog;

class BlogModelRepository implements BlogRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Blog::select($columns)->latest()
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Blog
    {
        return Blog::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        $blog = new Blog($data);

        return $blog->save() ? $blog : null;
    }

    public function update(array $data, Blog $blog, bool $updateTranslations = false): mixed
    {
        return $blog->update($data);
    }

    /** @param array<int, int> $ids */
    public function findManyForDeletion(array $ids): Collection
    {
        return Blog::query()->whereIn('id', $ids)->lockForUpdate()->get();
    }

    /** @param array<int, int> $ids */
    public function deleteMany(array $ids): int
    {
        return Blog::destroy($ids);
    }
}
