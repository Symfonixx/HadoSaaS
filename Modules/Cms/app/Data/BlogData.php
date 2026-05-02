<?php

namespace Modules\Cms\Data;

use Closure;
use Illuminate\Http\UploadedFile;
use Modules\User\Enums\CmsStatus;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class BlogData extends Data
{
    public function __construct(
        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $title,

        #[Required, StringType, Rule('min:2', 'max:255')]
        public string $slug,

        #[Required, StringType, Rule('max:500')]
        public string $description,

        #[Required, StringType]
        public string $content,

        #[Nullable, StringType, Rule('max:70')]
        public ?string $meta_title = null,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $meta_description = null,

        #[Nullable, StringType, Rule('max:255')]
        public ?string $meta_keywords = null,

        #[Nullable]
        public UploadedFile|string|null $image = null,

        #[Nullable]
        public UploadedFile|string|null $meta_image = null,

        #[Nullable]
        public CmsStatus $status = CmsStatus::PUBLISHED,

        #[Nullable, BooleanType]
        public ?bool $featured = false,

        #[Required]
        public int $category_id = 0,
    ) {}

    /**
     * Override inferred rules: unions with string pull in a global `string` rule, which rejects file uploads.
     *
     * @return array<string, list<string|Closure>>
     */
    public static function rules(?ValidationContext $context = null): array
    {
        $imageOrPath = function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }
            if ($value instanceof UploadedFile) {
                if (! $value->isValid()) {
                    $fail(__('The :attribute is not a valid file.'));
                }

                return;
            }
            if (! is_string($value)) {
                $fail(__('The :attribute must be a string.'));
            }
        };

        return [
            'image' => ['nullable', $imageOrPath],
            'meta_image' => ['nullable', $imageOrPath],
        ];
    }
}
