<?php

namespace App\Http\Controllers;

use Cache;
use Modules\Base\Models\Country;

abstract class Controller
{
    final protected function setActive(string $key)
    {
        $active = view()->shared('active', []);
        $active[$key] = true;
        view()->share('active', $active);

        return $this;
    }

    final protected function withCountries()
    {
        $countries = Cache::rememberForever('countries', static function () {
            return Country::all();
        });
        view()->share('countries', $countries);

        return $this;
    }
}
