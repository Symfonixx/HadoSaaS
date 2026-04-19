<?php

namespace Modules\Support\Repositories\Subscriber;

use Illuminate\Pagination\LengthAwarePaginator;

interface SubscriberRepository
{
    public function paginate(): LengthAwarePaginator;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
