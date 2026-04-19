<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Base\Application\Seo\SeoApplicationService;

class SeoController extends Controller
{
    public function __construct(private readonly SeoApplicationService $seoService)
    {
        $this->setActive('settings');
    }

    public function index()
    {
        $this->setActive('seo');
        $seo = $this->seoService->allKeyValue();

        return view('base::admin.seo.index', compact('seo'));
    }

    public function store(Request $request)
    {
        $this->seoService->update(
            data: $request->input('data', []),
            updateTranslations: $request->boolean('update_translations')
        );

        return back();
    }
}
