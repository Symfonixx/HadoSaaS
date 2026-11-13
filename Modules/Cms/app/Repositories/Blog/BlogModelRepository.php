<?php

namespace Modules\Cms\Repositories\Blog;

use Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;
use Modules\Cms\Models\Blog;
use Modules\Core\Traits\FileTrait;

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
        $ids = $this->normalizeDeletionIds($ids);

        $queuedPathHashes = DB::transaction(function () use ($ids) {
            $blogs = Blog::query()->whereIn('id', $ids)->lockForUpdate()->get();
            if ($blogs->count() !== count($ids)) {
                $missing = array_values(array_diff($ids, $blogs->modelKeys()));
                throw (new ModelNotFoundException)->setModel(Blog::class, $missing);
            }

            $mediaPaths = $blogs->flatMap(fn (Blog $blog) => [$blog->image, $blog->meta_image])
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->unique()
                ->values();

            if (Blog::destroy($ids) !== count($ids)) {
                throw new \RuntimeException('The selected blogs could not all be deleted.');
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
