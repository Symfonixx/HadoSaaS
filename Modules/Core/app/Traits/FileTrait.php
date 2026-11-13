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
            return Storage::disk($disk)->putFileAs($dir, $file, $filename);
        }

        // Process and upload image
        $image = Image::read($file);
        if ($width) {
            $image->resize($width);
        }
        Storage::disk($disk)->put($dir.'/'.$filename, (string) $image->encode());

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

    protected function queueFileDeletionIfUnreferenced(?string $path, string $disk = 'public'): void
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

    /** Process durable deletion requests; failures remain queued for a later retry. */
    public function processPendingFileDeletions(): array
    {
        $completed = 0;
        $failed = 0;

        foreach (DB::table('pending_file_deletions')->orderBy('id')->get() as $pending) {
            try {
                if (! $this->isFileReferenced($pending->path)) {
                    $this->deleteFile($pending->path, $pending->disk);
                }

                DB::table('pending_file_deletions')->where('id', $pending->id)->delete();
                $completed++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return ['completed' => $completed, 'failed' => $failed];
    }

    private function isFileReferenced(string $path): bool
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
