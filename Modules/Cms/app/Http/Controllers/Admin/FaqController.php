<?php

namespace Modules\Cms\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Faq\Commands\UpsertFaqCommand;
use Modules\Cms\Application\Faq\FaqApplicationService;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Data\FaqData;
use Modules\Cms\Models\Faq;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\User\Enums\CmsStatus;

class FaqController extends Controller
{
    public function __construct(private readonly FaqApplicationService $faqService)
    {
        $this->setActive('cms');
        $this->setActive('faqs');
    }

    public function index()
    {
        $model = $this->faqService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'question', 'answer', 'rank', 'status', 'created_at',
        ]);

        return view('cms::admin.faq.index', compact('model'));
    }

    public function create()
    {
        return view('cms::admin.faq.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = FaqData::validate($this->preparePayload($request));

        $this->faqService->store(UpsertFaqCommand::fromValidated($data));

        return redirect()->route('admin.faqs.index');
    }

    public function edit(Faq $faq)
    {
        return view('cms::admin.faq.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');
        $data = FaqData::validate($this->preparePayload($request));

        $this->faqService->update($faq, UpsertFaqCommand::fromValidated($data, $updateTranslations));

        return redirect()->route('admin.faqs.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->faqService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'question' => (string) $request->input('question'),
            'answer' => (string) $request->input('answer'),
            'rank' => (int) $request->input('rank', 0),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
        ];
    }
}
