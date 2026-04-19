<?php

namespace Modules\Core\Services\Translation;

use Modules\Core\Contracts\Translation\TranslatorInterface;

class GoogleTranslatorService implements TranslatorInterface
{
    public function translate(string $targetLanguage, string $content): string
    {
        return autoGoogleTranslator($targetLanguage, $content);
    }

    public function otherLanguages(): array
    {
        return otherLangs();
    }
}
