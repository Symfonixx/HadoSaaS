<?php

namespace Tests\Feature\Architecture;

use Modules\Base\Repositories\Log\LogRepository;
use Modules\Base\Repositories\Seo\SeoRepository;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Support\Repositories\ContactForm\ContactFormRepository;
use Modules\Support\Repositories\Subscriber\SubscriberRepository;
use Tests\TestCase;

class ServiceBindingTest extends TestCase
{
    public function test_core_cross_cutting_contracts_resolve_from_container(): void
    {
        $this->assertInstanceOf(TranslatorInterface::class, app(TranslatorInterface::class));
        $this->assertInstanceOf(FlashMessengerInterface::class, app(FlashMessengerInterface::class));
    }

    public function test_base_and_support_repositories_resolve_from_container(): void
    {
        $this->assertInstanceOf(SettingsRepository::class, app(SettingsRepository::class));
        $this->assertInstanceOf(SeoRepository::class, app(SeoRepository::class));
        $this->assertInstanceOf(LogRepository::class, app(LogRepository::class));
        $this->assertInstanceOf(SubscriberRepository::class, app(SubscriberRepository::class));
        $this->assertInstanceOf(ContactFormRepository::class, app(ContactFormRepository::class));
    }
}
