<?php

namespace Modules\Core\Application\Files;

enum FileCleanupResult: string
{
    case Complete = 'complete';
    case Pending = 'pending';
}
