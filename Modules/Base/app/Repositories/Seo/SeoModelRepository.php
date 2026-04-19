<?php

namespace Modules\Base\Repositories\Seo;

use Illuminate\Support\Collection;
use Modules\Base\Models\Seo;

class SeoModelRepository implements SeoRepository
{
    public function allKeyValue(): Collection
    {
        return Seo::query()->pluck('value', 'key');
    }

    public function set(string $key, string $value, bool $updateTranslations = true): bool
    {
        return Seo::set($key, $value, $updateTranslations);
    }
}
