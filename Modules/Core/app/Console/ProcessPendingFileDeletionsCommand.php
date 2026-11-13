<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Traits\FileTrait;

class ProcessPendingFileDeletionsCommand extends Command
{
    use FileTrait;

    protected $signature = 'files:cleanup-pending';

    protected $description = 'Retry queued file deletions that previously failed.';

    public function handle(): int
    {
        $result = $this->processPendingFileDeletions();

        if ($result['failed'] > 0) {
            $this->components->error($result['failed'].' queued file deletion(s) still need cleanup.');

            return self::FAILURE;
        }

        $this->components->info($result['completed'].' queued file deletion(s) processed.');

        return self::SUCCESS;
    }
}
