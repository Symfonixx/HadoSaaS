<?php

namespace Modules\Base\Repositories\Seo;

use Illuminate\Support\Collection;

interface SeoRepository
{
    public function allKeyValue(): Collection;

    public function set(string $key, string $value, bool $updateTranslations = true): bool;
}
