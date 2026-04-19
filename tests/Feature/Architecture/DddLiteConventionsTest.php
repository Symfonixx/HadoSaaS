<?php

namespace Tests\Feature\Architecture;

use Tests\TestCase;

class DddLiteConventionsTest extends TestCase
{
    public function test_ddd_lite_guideline_document_exists(): void
    {
        $this->assertFileExists(base_path('DDD_LITE_ARCHITECTURE.md'));
    }

    public function test_core_contract_bindings_are_registered(): void
    {
        $provider = file_get_contents(base_path('Modules/Core/app/Providers/CoreServiceProvider.php'));

        $this->assertStringContainsString('TranslatorInterface::class', $provider);
        $this->assertStringContainsString('GoogleTranslatorService::class', $provider);
        $this->assertStringContainsString('FlashMessengerInterface::class', $provider);
        $this->assertStringContainsString('SessionFlashMessenger::class', $provider);
    }

    public function test_cms_repositories_are_not_http_coupled(): void
    {
        $blogRepository = file_get_contents(base_path('Modules/Cms/app/Repositories/Blog/BlogModelRepository.php'));
        $pageRepository = file_get_contents(base_path('Modules/Cms/app/Repositories/Page/PageModelRepository.php'));

        $this->assertStringNotContainsString('request()', $blogRepository);
        $this->assertStringNotContainsString('request()', $pageRepository);
        $this->assertStringNotContainsString('session()->flushMessage', $blogRepository);
        $this->assertStringNotContainsString('session()->flushMessage', $pageRepository);
    }

    public function test_log_model_scope_does_not_read_request_directly(): void
    {
        $logModel = file_get_contents(base_path('Modules/Base/app/Models/LogDb.php'));

        $this->assertStringNotContainsString('request()', $logModel);
    }

    public function test_controllers_delegate_to_application_services(): void
    {
        $blogController = file_get_contents(base_path('Modules/Cms/app/Http/Controllers/Admin/BlogController.php'));
        $pageController = file_get_contents(base_path('Modules/Cms/app/Http/Controllers/Admin/PageController.php'));
        $settingsController = file_get_contents(base_path('Modules/Base/app/Http/Controllers/Admin/SettingsController.php'));
        $seoController = file_get_contents(base_path('Modules/Base/app/Http/Controllers/Admin/SeoController.php'));

        $this->assertStringContainsString('BlogApplicationService', $blogController);
        $this->assertStringContainsString('PageApplicationService', $pageController);
        $this->assertStringContainsString('SettingsApplicationService', $settingsController);
        $this->assertStringContainsString('SeoApplicationService', $seoController);
    }
}
