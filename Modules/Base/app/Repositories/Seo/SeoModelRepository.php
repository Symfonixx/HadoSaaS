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

    public function translationsFor(string $key): array
    {
        return Seo::translationsFor($key);
    }

    public function translationsForBatch(array $keys): array
    {
        $seos = Seo::query()->whereIn('key', $keys)->get();
        $result = [];
        foreach ($seos as $seo) {
            $result[$seo->key] = $seo->getTranslations('value');
        }
        return $result;
    }

    public function setTranslations(string $key, array $translations): bool
    {
        return Seo::setTranslations($key, $translations);
    }
}
