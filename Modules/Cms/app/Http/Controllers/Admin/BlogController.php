<?php

namespace Modules\Cms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Blog\BlogApplicationService;
use Modules\Cms\Application\Blog\Commands\UpsertBlogCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Data\BlogData;
use Modules\Cms\Models\Blog;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\User\Enums\CmsStatus;

class BlogController extends Controller
{
    public function __construct(private readonly BlogApplicationService $blogService)
    {
        $this->setActive('cms');
        $this->setActive('blogs');
    }

    public function index()
    {
        $model = $this->blogService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'title', 'slug', 'image', 'status', 'featured', 'visits', 'category_id', 'created_at',
        ]);

        return view('cms::admin.blog.index', compact('model'));
    }

    public function create()
    {
        $categories = $this->blogService->categories();

        return view('cms::admin.blog.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = BlogData::validate([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'keywords' => $request->input('keywords'),
            'image' => $request->file('img'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
            'category_id' => $request->input('category_id'),
        ]);
        $this->blogService->store(UpsertBlogCommand::fromValidated($data));

        return redirect()->route('admin.blogs.index');
    }

    public function edit(Blog $blog)
    {
        $categories = $this->blogService->categories();

        return view('cms::admin.blog.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $data = BlogData::validate([
            'title' => $request->input('title'),
            'slug' => $blog->slug,
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'keywords' => $request->input('keywords'),
            'image' => $request->file('img'),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
            'featured' => $request->boolean('featured'),
            'category_id' => $request->input('category_id'),
        ]);
        $this->blogService->update($blog, UpsertBlogCommand::fromValidated($data, $updateTranslations));

        return redirect()->route('admin.blogs.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->blogService->deleteMulti($request->input('ids'));

        return back();
    }
}
