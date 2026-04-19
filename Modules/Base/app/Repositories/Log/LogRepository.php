<?php

namespace Modules\Base\Repositories\Log;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Base\Models\LogDb;

interface LogRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?LogDb;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
