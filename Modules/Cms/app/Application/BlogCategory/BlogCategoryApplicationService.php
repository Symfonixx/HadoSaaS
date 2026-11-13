<?php

namespace Modules\Cms\Application\BlogCategory;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Repositories\BlogCategory\BlogCategoryRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Contracts\Translation\TranslatorInterface;

class BlogCategoryApplicationService
{
    public function __construct(
        private readonly BlogCategoryRepository $repository,
        private readonly TranslatorInterface $translator,
        private readonly FlashMessengerInterface $flashMessenger,
    ) {}

    public function all(array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns);
    }

    public function store(array $data): void
    {
        if (! $this->repository->store($this->prepareName($data))) {
            throw new \RuntimeException('The blog category could not be created.');
        }
        $this->flashMessenger->success();
    }

    public function update(array $data, BlogCategory $category, bool $updateTranslations = false): void
    {
        if (! $this->repository->update($this->prepareName($data, $category, $updateTranslations), $category)) {
            throw new \RuntimeException('The blog category update did not persist.');
        }
        $this->flashMessenger->success();
    }

    public function deleteMulti(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            if ($this->repository->deleteMulti($ids) !== count(array_unique(array_map('intval', $ids)))) {
                throw new \RuntimeException('Not all selected blog categories could be deleted.');
            }
        });
        $this->flashMessenger->success();
    }

    private function prepareName(array $data, ?BlogCategory $category = null, bool $updateTranslations = true): array
    {
        $locale = app()->getLocale();
        $translations = $category?->getTranslations('name') ?? [];
        $name = $data['name'];
        $source = is_array($name) ? ($name[$locale] ?? reset($name) ?: '') : $name;
        $translations[$locale] = $source;

        if (is_array($name)) {
            foreach ($name as $language => $translation) {
                if ($translation) {
                    $translations[$language] = $translation;
                }
            }
        }

        if ($updateTranslations) {
            foreach ($this->translator->otherLanguages() as $language) {
                $translations[$language] = $this->translator->translate($language, $source);
            }
        }

        return array_merge($data, ['name' => $translations]);
    }
}
