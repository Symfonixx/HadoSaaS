<?php

namespace Modules\Core\Contracts\Flash;

interface FlashMessengerInterface
{
    public function success(?string $message = null): void;

    public function error(string $message): void;
}
