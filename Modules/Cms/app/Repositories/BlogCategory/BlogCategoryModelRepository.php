<?php

namespace Modules\Cms\Repositories\BlogCategory;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\BlogCategory;

class BlogCategoryModelRepository implements BlogCategoryRepository {

    public function all(array $columns = ['*']): LengthAwarePaginator {
        return BlogCategory::select($columns)->latest()->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?BlogCategory {
        return BlogCategory::find($id, $columns);
    }

    public function store(array $data): mixed {
        $category = new BlogCategory($data);

        return $category->save() ? $category : null;
    }

    public function update(array $data, BlogCategory $category, bool $updateTranslations = false): mixed {
        return $category->update($data);
    }

    public function deleteMulti(array $ids): int {
        return BlogCategory::destroy($ids);
    }
}
