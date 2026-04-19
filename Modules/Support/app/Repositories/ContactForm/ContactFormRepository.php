<?php

namespace Modules\Support\Repositories\ContactForm;

use Illuminate\Pagination\LengthAwarePaginator;

interface ContactFormRepository
{
    public function paginate(): LengthAwarePaginator;

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): bool;
}
