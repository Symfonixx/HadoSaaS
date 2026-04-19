<?php

namespace Modules\Cms\Repositories\Blog;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Blog;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;

class BlogModelRepository implements BlogRepository
{
    use ExceptionHandlerTrait, FileTrait;

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
        return $this->execute(function () use ($data) {
            Blog::create($data);
        });
    }

    public function update(array $data, Blog $blog, bool $updateTranslations = false): mixed
    {
        return $this->execute(function () use ($data, $blog) {
            $blog->update($data);

            return true;
        });
    }

    public function deleteMulti(array $ids): ?bool
    {
        return $this->execute(function () use ($ids) {
            $images = Blog::whereIn('id', $ids)->pluck('image')->filter()->toArray();
            Blog::destroy($ids);
            $this->deleteFile($images);

            return true;
        });
    }
}
