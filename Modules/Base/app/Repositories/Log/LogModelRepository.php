<?php

namespace Modules\Base\Repositories\Log;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Base\Models\LogDb;

class LogModelRepository implements LogRepository
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return LogDb::query()
            ->when(! empty($filters['fLevel']), fn ($query) => $query->where('level_name', $filters['fLevel']))
            ->when(! empty($filters['fDate']), function ($query) use ($filters) {
                $date = (int) $filters['fDate'];
                $query->when($date === 1, fn ($subQuery) => $subQuery->whereDate('created_at', Carbon::today()))
                    ->when($date === 2, fn ($subQuery) => $subQuery->whereDate('created_at', Carbon::yesterday()))
                    ->when($date === 3, fn ($subQuery) => $subQuery->whereDate('created_at', '>', Carbon::now()->subWeek()));
            })
            ->latest()
            ->paginate(config('core.page_size'));
    }

    public function find(int $id): ?LogDb
    {
        return LogDb::query()->find($id);
    }

    public function deleteMulti(array $ids): bool
    {
        LogDb::destroy($ids);

        return true;
    }
}
