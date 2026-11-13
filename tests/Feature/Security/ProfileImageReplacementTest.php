<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImageReplacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_image_replacement_commits_then_cleans_up_the_old_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/old-profile.jpg', 'old-image');
        $user = $this->createAdmin('profile-success@example.test', 'users/old-profile.jpg');

        $this->actingAs($user)->put(route('admin.profile.update'), [
            'name' => 'Updated Profile',
            'email' => $user->email,
            'img' => UploadedFile::fake()->image('new-profile.jpg'),
        ])->assertRedirect(route('admin.profile.edit'))->assertSessionHas('success');

        $updated = $user->fresh();
        $this->assertNotSame('users/old-profile.jpg', $updated->img);
        Storage::disk('public')->assertExists($updated->img);
        Storage::disk('public')->assertMissing('users/old-profile.jpg');
        $this->assertDatabaseMissing('pending_file_deletions', ['path' => 'users/old-profile.jpg']);
    }

    public function test_failed_profile_update_preserves_the_old_image_and_cleans_staged_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/old-profile.jpg', 'old-image');
        $user = $this->createAdmin('profile-failure@example.test', 'users/old-profile.jpg');
        $event = 'eloquent.updating: '.User::class;
        Event::listen($event, fn () => throw new \RuntimeException('simulated profile persistence failure'));

        try {
            $this->actingAs($user)->put(route('admin.profile.update'), [
                'name' => 'Updated Profile',
                'email' => $user->email,
                'img' => UploadedFile::fake()->image('new-profile.jpg'),
            ])->assertInternalServerError();
        } finally {
            Event::forget($event);
        }

        $this->assertSame('users/old-profile.jpg', $user->fresh()->img);
        Storage::disk('public')->assertExists('users/old-profile.jpg');
        $this->assertCount(1, Storage::disk('public')->allFiles('users'));
        $this->assertSame(0, DB::table('pending_file_deletions')->count());
    }

    private function createAdmin(string $email, string $image): User
    {
        return User::query()->create([
            'name' => 'Profile User',
            'email' => $email,
            'mobile' => '0905000000'.random_int(400, 999),
            'password' => bcrypt('Strong-password-42!'),
            'type' => 'admin',
            'img' => $image,
        ]);
    }
}
