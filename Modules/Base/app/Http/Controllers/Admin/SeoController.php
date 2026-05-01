<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Base\Application\Seo\SeoApplicationService;
use Modules\Base\Repositories\Settings\SettingsRepository;

class SeoController extends Controller
{
    public function __construct(
        private readonly SeoApplicationService $seoService,
        private readonly SettingsRepository $settingsRepository,
    ) {
        $this->setActive('settings');
    }

    public function index()
    {
        $this->setActive('seo');
        $seo = $this->seoService->allKeyValue();
        $robotsTxt = (string) ($this->settingsRepository->get('robots_txt') ?: "User-agent: *\nDisallow:");

        return view('base::admin.seo.index', compact('seo', 'robotsTxt'));
    }

    public function store(Request $request)
    {
        $this->seoService->update(
            data: $request->input('data', []),
            updateTranslations: $request->boolean('update_translations')
        );

        if ($request->has('robots_txt')) {
            $this->settingsRepository->set('robots_txt', (string) $request->input('robots_txt', ''));
            cache()->forget('settings');
        }

        return back();
    }
}
