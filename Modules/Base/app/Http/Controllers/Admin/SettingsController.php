<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Base\Application\Settings\SettingsApplicationService;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsApplicationService $settingsService)
    {
        $this->setActive('settings');
    }

    public function index()
    {
        $this->setActive('websiteConfigurations');
        $settings = $this->settingsService->allKeyValue();

        return view('base::admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $mediaPaths = (array) $request->input('imgs_media', []);
        $this->settingsService->update(
            $request->file('imgs', []),
            $request->input('data', []),
            $mediaPaths
        );

        return back();
    }
}
