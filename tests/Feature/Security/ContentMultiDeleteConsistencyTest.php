<?php

namespace Tests\Feature\Security;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Models\Page;
use Modules\Cms\Application\Blog\BlogApplicationService;
use Modules\Cms\Application\Page\PageApplicationService;
use Modules\Cms\Repositories\Blog\BlogRepository;
use Modules\Cms\Repositories\Blog\BlogModelRepository;
use Modules\Cms\Repositories\Page\PageRepository;
use Modules\Cms\Repositories\Page\PageModelRepository;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Application\Files\PendingFileDeletionService;
use Tests\TestCase;

class ContentMultiDeleteConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_blog_or_page_ids_abort_without_partial_deletion(): void
    {
        Storage::fake('public');
        $blog = $this->createBlog('blogs/missing-check.jpg');
        $page = $this->createPage('pages/missing-check.jpg');
        Storage::disk('public')->put('blogs/missing-check.jpg', 'blog-image');
        Storage::disk('public')->put('pages/missing-check.jpg', 'page-image');

        try {
            $this->deleteBlog([$blog->id, 999999]);
            $this->fail('Expected a missing blog ID to abort the transaction.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseHas('blogs', ['id' => $blog->id]);
        }

        try {
            $this->deletePage([$page->id, 999999]);
            $this->fail('Expected a missing page ID to abort the transaction.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseHas('pages', ['id' => $page->id]);
        }

        $this->assertSame(0, DB::table('pending_file_deletions')->count());
        Storage::disk('public')->assertExists('blogs/missing-check.jpg');
        Storage::disk('public')->assertExists('pages/missing-check.jpg');
    }

    public function test_duplicate_ids_are_normalized_for_blog_and_page_deletion(): void
    {
        Storage::fake('public');
        $blog = $this->createBlog('blogs/duplicate-check.jpg');
        $page = $this->createPage('pages/duplicate-check.jpg');
        Storage::disk('public')->put('blogs/duplicate-check.jpg', 'blog-image');
        Storage::disk('public')->put('pages/duplicate-check.jpg', 'page-image');

        $this->deleteBlog([$blog->id, (string) $blog->id]);
        $this->deletePage([$page->id, (string) $page->id]);

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        Storage::disk('public')->assertMissing('blogs/duplicate-check.jpg');
        Storage::disk('public')->assertMissing('pages/duplicate-check.jpg');
    }

    public function test_database_delete_failure_rolls_back_rows_and_cleanup_requests(): void
    {
        $blog = $this->createBlog('blogs/db-failure.jpg');
        $page = $this->createPage('pages/db-failure.jpg');
        $blogEvent = 'eloquent.deleting: '.Blog::class;
        $pageEvent = 'eloquent.deleting: '.Page::class;
        Event::listen($blogEvent, fn () => throw new \RuntimeException('Simulated blog delete failure.'));
        Event::listen($pageEvent, fn () => throw new \RuntimeException('Simulated page delete failure.'));

        try {
            try {
                $this->deleteBlog([$blog->id]);
                $this->fail('Expected a blog database failure.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('Simulated blog delete failure.', $exception->getMessage());
            }

            try {
                $this->deletePage([$page->id]);
                $this->fail('Expected a page database failure.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('Simulated page delete failure.', $exception->getMessage());
            }
        } finally {
            Event::forget($blogEvent);
            Event::forget($pageEvent);
        }

        $this->assertDatabaseHas('blogs', ['id' => $blog->id]);
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
        $this->assertSame(0, DB::table('pending_file_deletions')->count());
    }

    public function test_failed_file_cleanup_is_queued_and_can_be_retried(): void
    {
        $blog = $this->createBlog('blogs/retry.jpg');
        $failedDisk = Mockery::mock();
        $failedDisk->shouldReceive('exists')->once()->with('blogs/retry.jpg')->andReturn(true);
        $failedDisk->shouldReceive('delete')->once()->with('blogs/retry.jpg')->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($failedDisk);

        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('error')->once();
        $flash->shouldNotReceive('success');
        $this->deleteBlog([$blog->id], $flash);
        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
        $this->assertDatabaseHas('pending_file_deletions', ['disk' => 'public', 'path' => 'blogs/retry.jpg']);

        $retryDisk = Mockery::mock();
        $retryDisk->shouldReceive('exists')->once()->with('blogs/retry.jpg')->andReturn(true);
        $retryDisk->shouldReceive('delete')->once()->with('blogs/retry.jpg')->andReturn(true);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($retryDisk);

        $result = (new class
        {
            use \Modules\Core\Traits\FileTrait;
        })->processPendingFileDeletions();

        $this->assertSame(0, $result);
        $this->assertDatabaseMissing('pending_file_deletions', ['path' => 'blogs/retry.jpg']);
    }

    public function test_page_file_cleanup_failure_is_queued_for_retry(): void
    {
        $page = $this->createPage('pages/retry.jpg');
        $disk = Mockery::mock();
        $disk->shouldReceive('exists')->once()->with('pages/retry.jpg')->andReturn(true);
        $disk->shouldReceive('delete')->once()->with('pages/retry.jpg')->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldReceive('error')->once();
        $flash->shouldNotReceive('success');
        $this->deletePage([$page->id], $flash);
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseHas('pending_file_deletions', ['disk' => 'public', 'path' => 'pages/retry.jpg']);
    }

    public function test_outbox_row_removal_failure_propagates_and_retry_is_safe_after_file_delete(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('blogs/outbox-row-failure.jpg', 'image');
        $id = DB::table('pending_file_deletions')->insertGetId([
            'disk' => 'public',
            'path' => 'blogs/outbox-row-failure.jpg',
            'path_hash' => hash('sha256', 'blogs/outbox-row-failure.jpg'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $processor = new class
        {
            use \Modules\Core\Traits\FileTrait;

            protected function removePendingFileDeletion(int $id): void
            {
                throw new \RuntimeException('outbox row removal failed');
            }
        };
        try {
            $processor->processPendingFileDeletions();
            $this->fail('Expected the database failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('outbox row removal failed', $exception->getMessage());
        }
        Storage::disk('public')->assertMissing('blogs/outbox-row-failure.jpg');
        $this->assertDatabaseHas('pending_file_deletions', ['id' => $id]);

        $this->assertSame(0, app(PendingFileDeletionService::class)->processPendingFileDeletions());
        $this->assertDatabaseMissing('pending_file_deletions', ['id' => $id]);
    }

    public function test_shared_content_image_is_not_deleted_while_another_record_references_it(): void
    {
        Storage::fake('public');
        $blog = $this->createBlog('shared/content.jpg');
        $page = $this->createPage('shared/content.jpg');
        Storage::disk('public')->put('shared/content.jpg', 'shared-image');

        $this->deleteBlog([$blog->id]);
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
        Storage::disk('public')->assertExists('shared/content.jpg');
    }

    public function test_file_trait_rejects_unsafe_paths_and_throws_when_storage_deletion_fails(): void
    {
        $files = new class
        {
            use \Modules\Core\Traits\FileTrait;

            public function remove(string $path): void
            {
                $this->deleteFile($path);
            }
        };

        try {
            $files->remove('../outside.jpg');
            $this->fail('Expected path traversal to be rejected.');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $disk = Mockery::mock();
        $disk->shouldReceive('exists')->once()->with('missing-delete.jpg')->andReturn(true);
        $disk->shouldReceive('delete')->once()->with('missing-delete.jpg')->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

        $this->expectException(\RuntimeException::class);
        $files->remove('missing-delete.jpg');
    }

    private function createBlog(string $image): Blog
    {
        $category = BlogCategory::query()->create([
            'name' => ['en' => 'Security Test'],
            'slug' => 'security-test-'.uniqid(),
        ]);

        return Blog::query()->create([
            'category_id' => $category->id,
            'title' => ['en' => 'Security Test'],
            'slug' => 'security-test-'.uniqid(),
            'description' => ['en' => 'Description'],
            'content' => ['en' => 'Content'],
            'image' => $image,
            'meta_image' => $image,
        ]);
    }

    private function createPage(string $image): Page
    {
        return Page::query()->create([
            'title' => ['en' => 'Security Test'],
            'slug' => 'security-test-'.uniqid(),
            'content' => ['en' => 'Content'],
            'image' => $image,
            'meta_image' => $image,
        ]);
    }

    private function deleteBlog(array $ids, ?FlashMessengerInterface $flash = null): void
    {
        if ($flash === null) {
            $flash = Mockery::mock(FlashMessengerInterface::class);
            $flash->shouldReceive('success')->zeroOrMoreTimes();
            $flash->shouldReceive('error')->zeroOrMoreTimes();
        }
        (new BlogApplicationService(app(BlogRepository::class), Mockery::mock(ContentPayloadBuilder::class), $flash, app(PendingFileDeletionService::class)))->deleteMulti($ids);
    }

    private function deletePage(array $ids, ?FlashMessengerInterface $flash = null): void
    {
        if ($flash === null) {
            $flash = Mockery::mock(FlashMessengerInterface::class);
            $flash->shouldReceive('success')->zeroOrMoreTimes();
            $flash->shouldReceive('error')->zeroOrMoreTimes();
        }
        (new PageApplicationService(app(PageRepository::class), Mockery::mock(ContentPayloadBuilder::class), $flash, app(PendingFileDeletionService::class)))->deleteMulti($ids);
    }
}
