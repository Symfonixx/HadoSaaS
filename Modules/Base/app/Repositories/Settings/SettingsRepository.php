<?php

namespace Modules\Base\Repositories\Settings;

use Illuminate\Support\Collection;

interface SettingsRepository
{
    public function allKeyValue(): Collection;

    public function get(string $key, ?string $default = null): mixed;

    public function set(string $key, ?string $value): bool;
}
