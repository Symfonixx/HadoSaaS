<?php

namespace Modules\Support\Repositories\Subscriber;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Support\Models\Subscriber;

class SubscriberModelRepository implements SubscriberRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return Subscriber::query()->latest()->paginate(config('core.page_size'));
    }

    public function deleteMulti(array $ids): bool
    {
        Subscriber::destroy($ids);

        return true;
    }
}
