<?php

namespace Modules\Cms\Application\Shared\Support;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Core\Traits\FileTrait;

class ContentPayloadBuilder
{
    use FileTrait;

    public function __construct(private readonly TranslatorInterface $translator) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function build(array $data, string $uploadPath, ?string $existingImage = null, ?Model $entity = null, bool $updateTranslations = true): array
    {
        $keywords = $this->parseKeywords($data['keywords'] ?? null);
        $locale = app()->getLocale();

        $transTitle = $entity?->getTranslations('title') ?? [];
        $transDescription = $entity?->getTranslations('description') ?? [];
        $transKeywords = $entity?->getTranslations('keywords') ?? [];
        $transContent = $entity?->getTranslations('content') ?? [];

        $title = (string) $data['title'];
        $description = (string) ($data['description'] ?? '');
        $content = (string) $data['content'];

        $transTitle[$locale] = $title;
        $transDescription[$locale] = $description;
        $transKeywords[$locale] = $keywords;
        $transContent[$locale] = $content;

        if ($updateTranslations) {
            foreach ($this->translator->otherLanguages() as $language) {
                try {
                    $transTitle[$language] = $this->translator->translate($language, $title);
                    $transDescription[$language] = $this->translator->translate($language, $description);
                    $transKeywords[$language] = $this->translator->translate($language, $keywords);
                    $transContent[$language] = $this->translator->translate($language, $content);
                } catch (Exception $exception) {
                    Log::error($exception->getMessage());
                }
            }
        }

        return array_merge($data, [
            'image' => $this->resolveImagePath($data['image'] ?? null, $uploadPath, $data['slug'], $existingImage),
            'title' => $transTitle,
            'description' => $transDescription,
            'keywords' => $transKeywords,
            'content' => $transContent,
            'status' => $data['status'],
            'featured' => (int) ($data['featured'] ?? false),
        ]);
    }

    private function resolveImagePath(?UploadedFile $image, string $uploadPath, string $slug, ?string $existingImage): ?string
    {
        if (! $image) {
            return $existingImage;
        }

        return $this->upload($image, $uploadPath, $slug, $existingImage);
    }

    private function parseKeywords(null|string|array $keywordsInput): string
    {
        if (! $keywordsInput) {
            return '';
        }

        if (is_array($keywordsInput)) {
            return implode(', ', array_filter($keywordsInput));
        }

        $decoded = json_decode($keywordsInput, true);
        if (! is_array($decoded)) {
            return $keywordsInput;
        }

        return implode(', ', array_column($decoded, 'value'));
    }
}
