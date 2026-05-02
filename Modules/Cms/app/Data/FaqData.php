<?php

namespace Modules\Cms\Data;

use Modules\User\Enums\CmsStatus;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class FaqData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $question,

        #[Required, StringType, Rule('min:2')]
        public string $answer,

        #[Required, IntegerType, Rule('min:0')]
        public int $rank = 0,

        #[Nullable]
        public CmsStatus $status = CmsStatus::PUBLISHED,
    ) {}
}
