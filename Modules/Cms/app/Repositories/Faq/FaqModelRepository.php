<?php

namespace Modules\Cms\Repositories\Faq;

use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Faq;

class FaqModelRepository implements FaqRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator
    {
        return Faq::select($columns)
            ->when(isset($filters['publish']) && $filters['publish'] !== null && $filters['publish'] !== '',
                fn ($q) => $q->where('status', $filters['publish']))
            ->orderBy('rank')
            ->latest()
            ->paginate(Config::get('core.page_size', 10));
    }

    public function find(int $id, array $columns = ['*']): ?Faq
    {
        return Faq::find($id, $columns);
    }

    public function store(array $data): mixed
    {
        $faq = new Faq($data);

        return $faq->save() ? $faq : null;
    }

    public function update(array $data, Faq $faq, bool $updateTranslations = false): mixed
    {
        return $faq->update($data);
    }

    public function deleteMulti(array $ids): int
    {
        return Faq::destroy($ids);
    }
}
