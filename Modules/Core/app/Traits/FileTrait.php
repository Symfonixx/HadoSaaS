<?php

namespace Modules\Core\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;

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
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Generates a sanitized, secure filename.
     */
    /**
     * Deletes an entire directory from the specified disk.
     */
    public function deleteDir(string $dir, string $disk = 'public'): void
    {
        if ($dir === '' || str_contains($dir, '..') || str_starts_with($dir, '/') || str_contains($dir, '\\')) {
            throw new \InvalidArgumentException('Invalid storage directory.');
        }
        Storage::disk($disk)->deleteDirectory($dir);
    }
}
