<?php

namespace Modules\Core\Application\Files;

use Illuminate\Support\Facades\DB;
use Modules\Core\Traits\FileTrait;

class PendingFileDeletionService
{
    use FileTrait;

    /** @param array<int, int|string> $ids @return array<int, int> */
    public function normalizeIds(array $ids): array
    {
        if ($ids === []) {
            throw new \InvalidArgumentException('At least one record ID is required for deletion.');
        }

        $normalized = [];
        foreach ($ids as $id) {
            if ((! is_int($id) && (! is_string($id) || ! ctype_digit($id))) || (int) $id < 1) {
                throw new \InvalidArgumentException('Deletion IDs must be positive integers.');
            }
            $normalized[(int) $id] = (int) $id;
        }

        return array_values($normalized);
    }

    /** @param array<int, string> $paths */
    public function queueUnreferenced(array $paths, string $disk = 'public'): void
    {
        foreach (array_unique($paths) as $path) {
            $this->queueFileDeletionIfUnreferenced($path, $disk);
        }
    }

    /** @param array<int, string> $paths */
    public function processAndReport(array $paths, string $disk = 'public'): FileCleanupResult
    {
        $this->processPendingFileDeletions();

        $hashes = array_map(static fn (string $path) => hash('sha256', $path), array_unique($paths));
        $pending = $hashes !== [] && DB::table('pending_file_deletions')
            ->where('disk', $disk)
            ->whereIn('path_hash', $hashes)
            ->exists();

        return $pending ? FileCleanupResult::Pending : FileCleanupResult::Complete;
    }
}
