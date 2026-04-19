<?php

namespace Modules\Cms\Application\Page\Commands;

class UpsertPageCommand
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly bool $updateTranslations = false
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated, bool $updateTranslations = false): self
    {
        return new self($validated, $updateTranslations);
    }
}
