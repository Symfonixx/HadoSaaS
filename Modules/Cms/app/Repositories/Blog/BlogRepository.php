<?php

namespace Modules\Cms\Repositories\Blog;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Blog;

interface BlogRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Blog;

    public function store(array $data): mixed;

    public function update(array $data, Blog $blog, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
