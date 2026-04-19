<?php

namespace Modules\Base\Models;

use Exception;
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

    /**
     * Set SEO value by key.
     */
    public static function set(string $key, string $value, bool $updateTranslations = true): bool
    {
        $seo = self::getAllSeoEntries();

        $model = $seo->firstWhere('key', $key);

        $translations = $model?->getTranslations('value') ?? [];
        $translations[app()->getLocale()] = $value;

        if ($updateTranslations) {
            foreach (otherLangs() as $lang) {
                try {
                    $translations[$lang] = autoGoogleTranslator($lang, $value);
                } catch (Exception $e) {
                    session()->flushMessage(false, $e->getMessage(), $e);
                }
            }
        }

        if ($model) {
            $model->update(['value' => $translations]);
        } else {
            $model = self::create(['key' => $key, 'value' => $translations]);
            $seo->push($model); // Update the cached collection.
        }

        self::cacheSeoEntries($seo);

        return true;
    }

    /**
     * Cache the SEO entries.
     */
    protected static function cacheSeoEntries(Collection $seo): void
    {
        Cache::put('seo_entries', $seo, now()->addMinutes(10));
    }
}
