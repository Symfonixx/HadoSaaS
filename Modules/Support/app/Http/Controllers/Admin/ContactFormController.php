<?php

namespace Modules\Support\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Support\Application\SupportAdmin\SupportInboxApplicationService;

class ContactFormController extends Controller
{
    public function __construct(private readonly SupportInboxApplicationService $supportInboxService)
    {
        $this->setActive('support');
        $this->setActive('contact_forms');
    }

    public function index()
    {
        $model = $this->supportInboxService->paginateContactForms();

        return view('support::admin.contact_form.index', compact('model'));
    }

    public function deleteMulti(DeleteMultiRequest $request)
    {
        $this->supportInboxService->deleteContactForms($request->ids);

        return redirect()->back();
    }
}
