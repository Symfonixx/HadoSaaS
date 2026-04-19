<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Base\Application\Log\LogApplicationService;
use Modules\Core\Http\Requests\DeleteMultiRequest;

class LogController extends Controller
{
    public function __construct(private readonly LogApplicationService $logService)
    {
        $this->setActive('logs');
    }

    public function index()
    {
        $model = $this->logService->paginate(request()->only(['fLevel', 'fDate']));

        return view('base::admin.log.index', compact('model'));
    }

    public function show(int $log)
    {
        $log = $this->logService->find($log);
        abort_if(! $log, 404);

        return view('base::admin.log.show', compact('log'));
    }

    public function deleteMulti(DeleteMultiRequest $request)
    {
        $this->logService->deleteMulti($request->input('ids'));

        return back();
    }
}
