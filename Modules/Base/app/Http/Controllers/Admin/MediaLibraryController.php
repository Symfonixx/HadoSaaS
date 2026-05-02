<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Base\Models\Media;
use Modules\Core\Traits\FileTrait;

class MediaLibraryController extends Controller
{
    use FileTrait;

    public function __construct()
    {
        $this->setActive('media_library');
    }

    public function index()
    {
        return view('base::admin.media.index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = Media::query()->latest('id');
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('path', 'like', '%'.$search.'%');
            });
        }

        $items = $query->paginate(24)->through(function (Media $media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->url,
                'path' => $media->path,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'created_at' => optional($media->created_at)->toDateTimeString(),
            ];
        });

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ]);

        $files = [];
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }
        if ($request->hasFile('files')) {
            $files = array_merge($files, (array) $request->file('files'));
        }

        abort_if(count($files) === 0, 422, __('No files uploaded'));

        $createdItems = [];
        foreach ($files as $file) {
            $path = $this->upload($file, 'media-library');
            $media = Media::query()->firstOrCreate(
                ['path' => $path],
                [
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'disk' => 'public',
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'user_id' => auth()->id(),
                ]
            );

            $createdItems[] = [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->url,
                'path' => $media->path,
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Uploaded successfully'),
                'items' => $createdItems,
            ]);
        }

        session()->flushMessage(true, __('The Operation Done Successfully'));

        return back();
    }

    public function deleteMulti(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:media,id'],
        ]);

        $items = Media::query()->whereIn('id', $payload['ids'])->get();
        foreach ($items as $item) {
            Storage::disk($item->disk)->delete($item->path);
            $item->delete();
        }

        return response()->json([
            'message' => __('The Operation Done Successfully'),
        ]);
    }

    public function destroy(Request $request, Media $media): JsonResponse|RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('The Operation Done Successfully'),
            ]);
        }

        session()->flushMessage(true, __('The Operation Done Successfully'));

        return back();
    }
}
