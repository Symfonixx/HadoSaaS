<?php

namespace Modules\Support\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Support\Application\SupportAdmin\SupportInboxApplicationService;

class SubscriberController extends Controller
{
    public function __construct(private readonly SupportInboxApplicationService $supportInboxService)
    {
        $this->setActive('support');
        $this->setActive('subscribers');
    }

    public function index()
    {
        $model = $this->supportInboxService->paginateSubscribers();

        return view('support::admin.subscriber.index', compact('model'));
    }

    public function deleteMulti(DeleteMultiRequest $request)
    {
        $this->supportInboxService->deleteSubscribers($request->ids);

        return redirect()->back();
    }
}
