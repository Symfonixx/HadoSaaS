<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Seo extends Model
{
    use HasTranslations;

    public $translatable = ['value'];

    public $timestamps = false;

    protected $table = 'seo';

    protected $fillable = ['key', 'value'];

    /**
     * Retrieve SEO value by key.
     *
     * @param  mixed|null  $default
     */
    public static function get(string $key, $default = null): mixed
    {
        $seo = self::getAllSeoEntries();

        $model = $seo->firstWhere('key', $key);

        return $model ? $model->value : $default ?? false;
    }

    /**
     * Get all SEO entries, with caching.
     */
    protected static function getAllSeoEntries(): Collection
    {
        return Cache::remember('seo_entries', now()->addMinutes(10), fn () => self::all());
    }

    /** @return array<string, string> */
    public static function translationsFor(string $key): array
    {
        return self::query()->where('key', $key)->first()?->getTranslations('value') ?? [];
    }

    /** Persist a fully prepared translation map without external service calls. */
    public static function setTranslations(string $key, array $translations): bool
    {
        $model = self::query()->where('key', $key)->first();
        if ($model) {
            return $model->update(['value' => $translations]);
        }

        $model = new self(['key' => $key, 'value' => $translations]);

        return $model->save();
    }

}
