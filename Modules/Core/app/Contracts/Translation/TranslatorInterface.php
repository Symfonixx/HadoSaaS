<?php

namespace Modules\Core\Contracts\Translation;

interface TranslatorInterface
{
    public function translate(string $targetLanguage, string $content): string;

    /**
     * @return array<int, string>
     */
    public function otherLanguages(): array;
}
