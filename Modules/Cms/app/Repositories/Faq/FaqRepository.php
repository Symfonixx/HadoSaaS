<?php

namespace Modules\Cms\Repositories\Faq;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Models\Faq;

interface FaqRepository
{
    public function all(array $columns = ['*'], array $filters = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Faq;

    public function store(array $data): mixed;

    public function update(array $data, Faq $faq, bool $updateTranslations = false): mixed;

    public function deleteMulti(array $ids): ?bool;
}
