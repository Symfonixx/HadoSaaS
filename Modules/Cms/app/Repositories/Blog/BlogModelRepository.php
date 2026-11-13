<?php

namespace Modules\Cms\Repositories\Blog;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Blog;
use Modules\Core\Traits\FileTrait;
use Illuminate\Support\Facades\DB;

class BlogModelRepository implements BlogRepository
{
    use FileTrait;

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
        return Blog::create($data);
    }

    public function update(array $data, Blog $blog, bool $updateTranslations = false): mixed
    {
        return $blog->update($data);
    }

    public function deleteMulti(array $ids): ?bool
    {
        return DB::transaction(function () use ($ids) {
            $blogs = Blog::whereIn('id', $ids)->get();
            $deleted = Blog::destroy($ids);
            $this->deleteFile($blogs->pluck('image')->filter()->values()->all());
            return $deleted === count($ids);
        });
    }
}
