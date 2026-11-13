<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected static $settingsCache;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    /**
     * Get the value of a setting by key, with an optional default.
     */
    public static function get(string $key, ?string $default = null)
    {
        self::loadSettingsCache();

        return self::$settingsCache[$key]->value ?? $default ?? false;
    }

    /**
     * Fetch all settings from the database if not already cached.
     */
    protected static function loadSettingsCache()
    {
        if (is_null(self::$settingsCache)) {
            self::$settingsCache = self::all()->keyBy('key');
        }
    }

    public static function resetCache(): void
    {
        self::$settingsCache = null;
    }

    /**
     * Set the value of a setting by key.
     */
    public static function set(string $key, string $value): bool
    {
        if (trim($key) === '') {
            throw new \InvalidArgumentException('Setting keys must not be empty.');
        }

        self::loadSettingsCache();

        if (! isset(self::$settingsCache[$key])) {
            $model = new self(['key' => $key, 'value' => $value]);
            if (! $model->save()) {
                return false;
            }
            self::$settingsCache[$key] = $model;
        } else {
            $model = self::$settingsCache[$key];
            if (! $model->update(['value' => $value])) {
                return false;
            }
        }

        return true;
    }
}
