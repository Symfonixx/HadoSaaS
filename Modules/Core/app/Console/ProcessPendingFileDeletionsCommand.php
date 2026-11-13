<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Application\Files\PendingFileDeletionService;

class ProcessPendingFileDeletionsCommand extends Command
{
    protected $signature = 'files:cleanup-pending';

    protected $description = 'Retry queued file deletions that previously failed.';

    public function handle(): int
    {
        $failed = app(PendingFileDeletionService::class)->processPendingFileDeletions();

        if ($failed > 0) {
            $this->components->error($failed.' queued file deletion(s) still need cleanup.');

            return self::FAILURE;
        }

        $this->components->info('Queued file deletions processed.');

        return self::SUCCESS;
    }
}
