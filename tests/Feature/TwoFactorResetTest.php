<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_artisan_command_can_reset_2fa_by_email(): void
    {
        $user = User::where('email', 'admin1@5758inc.com')->first();
        $user->google2fa_secret = 'TESTSECRET123';
        $user->save();

        $this->assertNotNull($user->fresh()->google2fa_secret);

        $this->artisan('2fa:reset', ['email' => 'admin1@5758inc.com'])
            ->assertSuccessful();

        $this->assertNull($user->fresh()->google2fa_secret);
    }

    public function test_artisan_command_can_reset_all_users(): void
    {
        User::query()->update(['google2fa_secret' => 'DUMMYSECRET']);

        $this->artisan('2fa:reset', ['--all' => true])
            ->expectsConfirmation('Apakah Anda yakin ingin me-reset 2FA untuk SEMUA pengguna?', 'yes')
            ->assertSuccessful();

        $this->assertEquals(0, User::whereNotNull('google2fa_secret')->count());
    }

    public function test_owner_can_view_users_list_and_reset_2fa(): void
    {
        $owner = User::where('role', User::ROLE_OWNER)->first();
        $admin = User::where('email', 'admin1@5758inc.com')->first();
        $admin->google2fa_secret = 'ACTIVE_SECRET';
        $admin->save();

        $response = $this->actingAs($owner)
            ->withSession(['auth.2fa.authenticated' => true])
            ->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna & Keamanan 2FA', false);
        $response->assertSee('admin1@5758inc.com');

        // Owner resets Admin's 2FA
        $postResponse = $this->actingAs($owner)
            ->withSession(['auth.2fa.authenticated' => true])
            ->post(route('users.reset-2fa', $admin));

        $postResponse->assertRedirect(route('users.index'));
        $postResponse->assertSessionHas('success');

        $this->assertNull($admin->fresh()->google2fa_secret);
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $response = $this->actingAs($admin)
            ->withSession(['auth.2fa.authenticated' => true])
            ->get(route('users.index'));

        $response->assertStatus(403);
    }
}
