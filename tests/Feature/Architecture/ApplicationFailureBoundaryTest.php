<?php

namespace Tests\Feature\Architecture;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Base\Application\Seo\SeoApplicationService;
use Modules\Base\Models\Seo;
use Modules\Base\Repositories\Seo\SeoRepository;
use Modules\Base\Application\Settings\SettingsApplicationService;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Base\Repositories\Settings\SettingsModelRepository;
use Modules\Base\Models\Settings;
use Modules\Cms\Application\BlogCategory\BlogCategoryApplicationService;
use Modules\Cms\Application\Blog\BlogApplicationService;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Repositories\BlogCategory\BlogCategoryRepository;
use Modules\Cms\Repositories\Blog\BlogRepository;
use Modules\Core\Application\Files\PendingFileDeletionService;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\User\Application\User\UserManagementApplicationService;
use Modules\User\app\Repositories\User\UserRepository;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationFailureBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_category_translation_failure_propagates_without_success_notification(): void
    {
        $repository = Mockery::mock(BlogCategoryRepository::class);
        $repository->shouldNotReceive('store');
        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('otherLanguages')->once()->andReturn(['fr']);
        $translator->shouldReceive('translate')->once()->andThrow(new \RuntimeException('translation failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        $this->expectExceptionMessage('translation failed');
        (new BlogCategoryApplicationService($repository, $translator, $flash))->store(['name' => 'Name', 'slug' => 'name']);
    }

    public function test_blog_category_repository_failure_does_not_flash_success(): void
    {
        $repository = Mockery::mock(BlogCategoryRepository::class);
        $repository->shouldReceive('update')->once()->andThrow(new \RuntimeException('category write failed'));
        $translator = Mockery::mock(TranslatorInterface::class);
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');
        $category = new \Modules\Cms\Models\BlogCategory;

        $this->expectExceptionMessage('category write failed');
        (new BlogCategoryApplicationService($repository, $translator, $flash))->update(['name' => 'Name'], $category, false);
    }

    public function test_blog_category_create_keeps_its_existing_redirect_response(): void
    {
        $this->withoutMiddleware();
        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('otherLanguages')->once()->andReturn([]);
        $this->app->instance(TranslatorInterface::class, $translator);
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('success')->times(3);
        $this->app->instance(FlashMessengerInterface::class, $flash);

        $this->post(route('admin.blogs_categories.store'), [
            'name' => 'Response Test',
            'slug' => 'response-test',
        ])->assertRedirect(route('admin.blogs_categories.index'));

        $category = \Modules\Cms\Models\BlogCategory::query()->where('slug', 'response-test')->firstOrFail();
        $this->put(route('admin.blogs_categories.update', $category->id), [
            'name' => 'Updated Response Test',
        ])->assertRedirect(route('admin.blogs_categories.index'));

        $this->from(route('admin.blogs_categories.index'))
            ->delete(route('admin.blogs_categories.deleteMulti'), ['ids' => [$category->id]])
            ->assertRedirect(route('admin.blogs_categories.index'));

        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }

    public function test_blog_delete_database_failure_does_not_flash_success(): void
    {
        $repository = Mockery::mock(BlogRepository::class);
        $repository->shouldReceive('findManyForDeletion')->once()->andThrow(new \RuntimeException('blog query failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');
        $flash->shouldNotReceive('error');

        $this->expectExceptionMessage('blog query failed');
        (new BlogApplicationService($repository, Mockery::mock(ContentPayloadBuilder::class), $flash, new PendingFileDeletionService()))->deleteMulti([1]);
    }

    public function test_settings_partial_database_failure_does_not_flash_success(): void
    {
        $event = 'eloquent.saving: '.Settings::class;
        Event::listen($event, function (Settings $setting): void {
            if ($setting->key === 'second') {
                throw new \RuntimeException('write failed');
            }
        });
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new SettingsApplicationService(new SettingsModelRepository(), $flash, new PendingFileDeletionService()))
                ->update(data: ['first' => 'one', 'second' => 'two']);
            $this->fail('Expected the second setting write to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('write failed', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_settings_persists_empty_values(): void
    {
        $repository = Mockery::mock(SettingsRepository::class);
        $repository->shouldReceive('set')->once()->with('website_name', '')->andReturn(true);
        $repository->shouldReceive('resetCache')->once();
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('success')->once();

        (new SettingsApplicationService($repository, $flash, new PendingFileDeletionService()))->update(data: ['website_name' => '']);
    }

    public function test_settings_accepts_an_empty_update(): void
    {
        $repository = Mockery::mock(SettingsRepository::class);
        $repository->shouldNotReceive('set');
        $repository->shouldReceive('resetCache')->once();
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('success')->once();

        (new SettingsApplicationService($repository, $flash, new PendingFileDeletionService()))->update();
    }

    public function test_settings_database_failure_removes_staged_replacement_and_preserves_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/old-logo.jpg', 'old image');
        $repository = Mockery::mock(SettingsRepository::class);
        $repository->shouldReceive('get')->once()->with('logo')->andReturn('settings/old-logo.jpg');
        $repository->shouldReceive('set')->once()->with('logo', Mockery::type('string'))->andThrow(new \RuntimeException('settings write failed'));
        $repository->shouldReceive('resetCache')->once();
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new SettingsApplicationService($repository, $flash, new PendingFileDeletionService()))
                ->update(images: ['logo' => UploadedFile::fake()->image('logo.jpg')]);
            $this->fail('Expected the setting write to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('settings write failed', $exception->getMessage());
        }

        Storage::disk('public')->assertExists('settings/old-logo.jpg');
        $this->assertCount(1, Storage::disk('public')->allFiles('settings'));
    }

    public function test_failed_old_file_removal_keeps_replacement_and_reports_pending_cleanup(): void
    {
        $repository = Mockery::mock(SettingsRepository::class);
        $repository->shouldReceive('get')->once()->with('logo')->andReturn('settings/old-logo.jpg');
        $repository->shouldReceive('set')->once()->with('logo', Mockery::type('string'))->andReturn(true);
        $repository->shouldReceive('resetCache')->once();
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('error')->once();
        $flash->shouldNotReceive('success');
        $disk = Mockery::mock();
        $disk->shouldReceive('put')->once()->andReturn(true);
        $disk->shouldReceive('exists')->once()->with('settings/old-logo.jpg')->andReturn(true);
        $disk->shouldReceive('delete')->once()->with('settings/old-logo.jpg')->andReturn(false);
        Storage::shouldReceive('disk')->twice()->with('public')->andReturn($disk);

        (new SettingsApplicationService($repository, $flash, new PendingFileDeletionService()))
            ->update(images: ['logo' => UploadedFile::fake()->image('logo.jpg')]);

        $this->assertDatabaseHas('pending_file_deletions', ['path' => 'settings/old-logo.jpg']);
    }

    public function test_seo_multi_key_write_failure_rolls_back_and_does_not_flash_success(): void
    {
        $repository = Mockery::mock(SeoRepository::class);
        $repository->shouldReceive('translationsFor')->twice()->andReturn([]);
        $repository->shouldReceive('setTranslations')->once()->with('first', ['en' => 'one'])->andReturnUsing(function ($key, $translations) {
            Seo::query()->create(['key' => $key, 'value' => $translations]);

            return true;
        });
        $repository->shouldReceive('setTranslations')->once()->with('second', ['en' => 'two'])->andThrow(new \RuntimeException('second write failed'));
        $translator = Mockery::mock(TranslatorInterface::class);
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new SeoApplicationService($repository, $flash, $translator, Mockery::mock(SettingsRepository::class)))->update(['first' => 'one', 'second' => 'two'], false);
            $this->fail('Expected the second write to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('second write failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('seo', 0);
    }

    public function test_seo_translation_failure_prevents_all_persistence(): void
    {
        $repository = Mockery::mock(SeoRepository::class);
        $repository->shouldReceive('translationsFor')->once()->with('first')->andReturn([]);
        $repository->shouldNotReceive('setTranslations');
        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('otherLanguages')->once()->andReturn(['fr']);
        $translator->shouldReceive('translate')->once()->andThrow(new \RuntimeException('translation failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        $this->expectExceptionMessage('translation failed');
        (new SeoApplicationService($repository, $flash, $translator, Mockery::mock(SettingsRepository::class)))->update(['first' => 'one'], true);
    }

    public function test_seo_persists_blank_values(): void
    {
        $repository = Mockery::mock(SeoRepository::class);
        $repository->shouldReceive('translationsFor')->once()->with('meta_title')->andReturn([]);
        $repository->shouldReceive('setTranslations')->once()->with('meta_title', ['en' => '   '])->andReturn(true);
        $translator = Mockery::mock(TranslatorInterface::class);
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('success')->once();

        (new SeoApplicationService($repository, $flash, $translator, Mockery::mock(SettingsRepository::class)))
            ->update(['meta_title' => '   '], false);
    }

    public function test_seo_and_robots_text_write_roll_back_together(): void
    {
        $seoRepository = Mockery::mock(SeoRepository::class);
        $seoRepository->shouldReceive('translationsFor')->once()->with('meta_title')->andReturn([]);
        $seoRepository->shouldReceive('setTranslations')->once()->andReturnUsing(function ($key, $translations) {
            Seo::query()->create(['key' => $key, 'value' => $translations]);

            return true;
        });
        $settingsRepository = Mockery::mock(SettingsRepository::class);
        $settingsRepository->shouldReceive('set')->once()->with('robots_txt', 'User-agent: *')->andThrow(new \RuntimeException('robots write failed'));
        $translator = Mockery::mock(TranslatorInterface::class);
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new SeoApplicationService($seoRepository, $flash, $translator, $settingsRepository))
                ->update(['meta_title' => 'Title'], false, 'User-agent: *');
            $this->fail('Expected robots text persistence to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('robots write failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('seo', 0);
    }

    public function test_super_admin_deletion_is_rejected_at_application_boundary(): void
    {
        $user = User::query()->create(['name' => 'Root', 'email' => 'root@example.test', 'mobile' => '0905000000311', 'password' => bcrypt('password')]);
        $user->assignRole(Role::query()->create(['name' => 'Admin', 'guard_name' => 'web']));
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldNotReceive('delete');
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        $this->expectException(AuthorizationException::class);
        (new UserManagementApplicationService($repository, $flash, new PendingFileDeletionService()))->delete($user);
    }

    public function test_user_deletion_database_failure_rolls_back_without_outbox_entry(): void
    {
        $user = User::query()->create(['name' => 'User', 'email' => 'delete@example.test', 'mobile' => '0905000000312', 'password' => bcrypt('password')]);
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('delete')->once()->andThrow(new \RuntimeException('delete failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new UserManagementApplicationService($repository, $flash, new PendingFileDeletionService()))->delete($user);
            $this->fail('Expected deletion failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('delete failed', $exception->getMessage());
        }

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('pending_file_deletions', 0);
    }

    public function test_user_file_cleanup_failure_keeps_outbox_work_and_does_not_flash_success(): void
    {
        $user = User::query()->create(['name' => 'User', 'email' => 'file-delete@example.test', 'mobile' => '0905000000313', 'password' => bcrypt('password'), 'img' => 'users/old.jpg']);
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('delete')->once()->andReturnUsing(fn (User $user) => (bool) $user->delete());
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('error')->once();
        $flash->shouldNotReceive('success');
        $disk = Mockery::mock();
        $disk->shouldReceive('exists')->once()->with('users/old.jpg')->andReturn(true);
        $disk->shouldReceive('delete')->once()->with('users/old.jpg')->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

        (new UserManagementApplicationService($repository, $flash, new PendingFileDeletionService()))->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('pending_file_deletions', ['path' => 'users/old.jpg']);
    }
}
