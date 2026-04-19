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

        // Convert request data to PageData DTO
        $data = PageData::validate([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'keywords' => $request->input('keywords'),
            'image' => $request->file('img'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
        ]);

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

        // Convert request data to PageData DTO
        $data = PageData::validate([
            'title' => $request->input('title'),
            'slug' => $page->slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'keywords' => $request->input('keywords'),
            'image' => $request->file('img'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
        ]);
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
}
