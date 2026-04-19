<?php

namespace Modules\Cms\Application\Shared\Queries;

class ContentListQuery
{
    public function __construct(
        public readonly mixed $publish = null,
        public readonly ?string $type = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'publish' => $this->publish,
            'type' => $this->type,
        ];
    }
}
