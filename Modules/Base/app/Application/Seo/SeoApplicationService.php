<?php

namespace Modules\Base\Application\Seo;

use Illuminate\Support\Collection;
use Modules\Base\Repositories\Seo\SeoRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class SeoApplicationService
{
    public function __construct(
        private readonly SeoRepository $seoRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function allKeyValue(): Collection
    {
        return $this->seoRepository->allKeyValue();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, bool $updateTranslations): void
    {
        foreach ($data as $key => $value) {
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $this->seoRepository->set($key, $value, $updateTranslations);
        }

        $this->flashMessenger->success();
    }
}
