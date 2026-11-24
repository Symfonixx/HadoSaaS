<?php

namespace Modules\Base\Repositories\Seo;

use Illuminate\Support\Collection;

interface SeoRepository
{
    public function allKeyValue(): Collection;

    /** @return array<string, string> */
    public function translationsFor(string $key): array;

    /**
     * Batch fetch translations for multiple keys in a single query.
     *
     * @param  string[]  $keys
     * @return array<string, array<string, string>>
     */
    public function translationsForBatch(array $keys): array;

    /** @param array<string, string> $translations */
    public function setTranslations(string $key, array $translations): bool;

    /** @param array<string, array<string, string>> $values */
    public function setTranslationsBatch(array $values): void;
}
