<?php

namespace Modules\Base\Repositories\Settings;

use Illuminate\Support\Collection;
use Modules\Base\Models\Settings;

class SettingsModelRepository implements SettingsRepository
{
    public function allKeyValue(): Collection
    {
        return Settings::query()->pluck('value', 'key');
    }

    public function get(string $key, ?string $default = null): mixed
    {
        return Settings::get($key, $default);
    }

    public function set(string $key, ?string $value): bool
    {
        return Settings::set($key, $value);
    }
}
