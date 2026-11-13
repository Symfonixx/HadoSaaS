<?php

namespace Modules\User\app\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[StringType, Min(3), Max(50)]
        public string $name,

        #[Email, Max(255)]
        public string $email,

        #[Regex('/^[0-9]{10,15}$/')]
        public string $mobile,

        #[Nullable, StringType, Min(6), Max(100)]
        public ?string $password,  // Make password nullable

        #[Nullable, StringType]
        public ?string $type = null,
    ) {}
}
