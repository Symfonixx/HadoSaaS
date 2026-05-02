<?php

namespace Modules\Cms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Page\Commands\UpsertPageCommand;
use Modules\Cms\Application\Page\PageApplicationService;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Data\PageData;
use Modules\Cms\Models\Page;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\User\Enums\CmsStatus;

class PageController extends Controller
{
    public function __construct(private readonly PageApplicationService $pageService)
    {
        $this->setActive('cms');
        $this->setActive('pages');
    }

    /**
     * Display a listing of pages.
     */
    public function index()
    {
        $model = $this->pageService->paginate(new ContentListQuery(
            publish: request()->query('publish'),
            type: request()->query('type')
        ), [
            'id', 'title', 'slug', 'image', 'status', 'featured', 'visits', 'created_at',
        ]);

        return view('cms::admin.page.index', compact('model'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('cms::admin.page.create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = PageData::validate($this->preparePayload($request));

        $this->pageService->store(UpsertPageCommand::fromValidated($data));

        return redirect()->route('admin.pages.index');
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        return view('cms::admin.page.edit', compact('page'));
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $payload = $this->preparePayload($request);
        $payload['slug'] = $page->slug;

        $data = PageData::validate($payload);

        $this->pageService->update($page, UpsertPageCommand::fromValidated($data, $updateTranslations));

        return redirect()->route('admin.pages.index');
    }

    /**
     * Remove multiple pages from storage.
     */
    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->pageService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'title' => (string) $request->input('title'),
            'slug' => (string) $request->input('slug'),
            'content' => (string) $request->input('content'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'image' => $request->file('img') ?: $request->input('img_media_path'),
            'meta_image' => $request->file('meta_img') ?: $request->input('meta_img_media_path'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
        ];
    }
}
