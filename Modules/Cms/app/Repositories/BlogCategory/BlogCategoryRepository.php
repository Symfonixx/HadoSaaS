<?php

namespace Modules\Cms\Repositories\BlogCategory;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\BlogCategory;

interface BlogCategoryRepository
{
    public function all(array $columns = ['*']): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?BlogCategory;

    public function store(array $data): mixed;

    public function update(array $data, BlogCategory $category, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
