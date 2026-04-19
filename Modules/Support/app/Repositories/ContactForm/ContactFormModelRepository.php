<?php

namespace Modules\Support\Repositories\ContactForm;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Support\Models\ContactForm;

class ContactFormModelRepository implements ContactFormRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return ContactForm::query()->latest()->paginate(config('core.page_size'));
    }

    public function deleteMulti(array $ids): bool
    {
        ContactForm::destroy($ids);

        return true;
    }
}
