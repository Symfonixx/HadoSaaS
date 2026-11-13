<?php

namespace Modules\Core\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

trait FileTrait
{
    /**
     * Uploads a file or image to the specified directory.
     */
    public function upload(
        UploadedFile $file,
        string $dir,
        ?string $name = null,
        ?string $old = null,
        ?int $width = null,
        string $disk = 'public'
    ): ?string {
        // Validate the file
        $extension = $this->validatedExtension($file);

        // Remove an old file if specified
        if ($old) {
            $this->deleteFile($old, $disk);
        }

        // Generate a secure filename
        $filename = bin2hex(random_bytes(20)).'.'.$extension;

        // Handle PDF files
        if ($extension === 'pdf') {
            $path = Storage::disk($disk)->putFileAs($dir, $file, $filename);
            if (! is_string($path) || $path === '') {
                throw new \RuntimeException('Could not store the uploaded file.');
            }

            return $path;
        }

        // Process and upload image
        $image = Image::read($file);
        if ($width) {
            $image->resize($width);
        }
        if (! Storage::disk($disk)->put($dir.'/'.$filename, (string) $image->encode())) {
            throw new \RuntimeException('Could not store the uploaded image.');
        }

        return $dir.'/'.$filename;
    }

    /**
     * Validates if a file is acceptable for upload.
     */
    private function validatedExtension(UploadedFile $file): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages(['file' => __('The selected file is not valid.')]);
        }

        $mime = $file->getMimeType();
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        if (! isset($types[$mime]) || ! in_array($mime, config('core.allowed_mime_types', array_keys($types)), true)) {
            throw ValidationException::withMessages(['file' => __('File type is not allowed.')]);
        }

        if ($mime !== 'application/pdf' && ! @getimagesize($file->getRealPath())) {
            throw ValidationException::withMessages(['file' => __('The uploaded image is invalid.')]);
        }

        return $types[$mime];
    }

    /**
     * Deletes a file from the specified disk.
     */
    public function deleteFile(string|array|\Illuminate\Support\Collection|null $filename, string $disk = 'public'): void
    {
        if ($filename instanceof \Illuminate\Support\Collection) {
            $filename = $filename->all();
        }
        foreach ((array) $filename as $path) {
            if (! is_string($path) || $path === '' || str_contains($path, '..') || str_starts_with($path, '/') || str_contains($path, '\\')) {
                throw new \InvalidArgumentException('Invalid stored file path.');
            }
            $storage = Storage::disk($disk);
            if ($storage->exists($path) && ! $storage->delete($path)) {
                throw new \RuntimeException("Could not delete stored file [{$path}].");
            }
        }
    }

    public function queueFileDeletionIfUnreferenced(?string $path, string $disk = 'public'): void
    {
        if ($path === null || trim($path) === '' || $this->isFileReferenced($path)) {
            return;
        }

        DB::table('pending_file_deletions')->insertOrIgnore([
            'disk' => $disk,
            'path' => $path,
            'path_hash' => hash('sha256', $path),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function normalizeDeletionIds(array $ids): array
    {
        if ($ids === []) {
            throw new \InvalidArgumentException('At least one record ID is required for deletion.');
        }

        $normalized = [];
        foreach ($ids as $id) {
            if (! is_int($id) && (! is_string($id) || ! ctype_digit($id))) {
                throw new \InvalidArgumentException('Deletion IDs must be positive integers.');
            }

            $id = (int) $id;
            if ($id < 1) {
                throw new \InvalidArgumentException('Deletion IDs must be positive integers.');
            }
            $normalized[$id] = $id;
        }

        return array_values($normalized);
    }

    /** Process durable deletion requests; storage failures remain queued for retry. */
    public function processPendingFileDeletions(): int
    {
        $failed = 0;

        foreach (DB::table('pending_file_deletions')->orderBy('id')->get() as $pending) {
            if (! $this->isFileReferenced($pending->path)) {
                try {
                    $this->deleteFile($pending->path, $pending->disk);
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                    continue;
                }
            }

            // Database failures must propagate. If storage deletion already succeeded,
            // the durable row remains and the next retry safely treats a missing file as done.
            $this->removePendingFileDeletion((int) $pending->id);
        }

        return $failed;
    }

    protected function isFileReferenced(string $path): bool
    {
        $references = [
            'blogs' => ['image', 'meta_image'],
            'pages' => ['image', 'meta_image'],
            'media' => ['path'],
            'users' => ['img'],
            'settings' => ['value'],
        ];

        foreach ($references as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column) && DB::table($table)->where($column, $path)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function removePendingFileDeletion(int $id): void
    {
        DB::table('pending_file_deletions')->where('id', $id)->delete();
    }

    /**
     * Deletes an entire directory from the specified disk.
     */
    public function deleteDir(string $dir, string $disk = 'public'): void
    {
        if ($dir === '' || str_contains($dir, '..') || str_starts_with($dir, '/') || str_contains($dir, '\\')) {
            throw new \InvalidArgumentException('Invalid storage directory.');
        }
        $storage = Storage::disk($disk);
        if ($storage->directoryExists($dir) && ! $storage->deleteDirectory($dir)) {
            throw new \RuntimeException("Could not delete storage directory [{$dir}].");
        }
    }
}
