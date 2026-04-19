<?php

namespace Modules\Core\Services\Flash;

use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class SessionFlashMessenger implements FlashMessengerInterface
{
    public function success(?string $message = null): void
    {
        session()->flushMessage(true, $message ?? '');
    }

    public function error(string $message): void
    {
        session()->flushMessage(false, $message);
    }
}
