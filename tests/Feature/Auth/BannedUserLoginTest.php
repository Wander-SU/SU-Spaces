<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannedUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_user_cannot_log_in(): void
    {
        $role = Role::query()->create([
            'role_name' => 'Student',
            'role_abbrev' => 'STD',
        ]);

        $user = User::factory()->create([
            'username' => 'banuser01',
            'active' => 0,
            'role_id' => $role->id,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post(route('login.attempt'), [
            'username' => $user->username,
            'password' => 'secret123',
        ]);

        $response
            ->assertSessionHasErrors([
                'username' => 'Your account has been banned and you can\'t log in.',
            ])
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_active_user_can_log_in(): void
    {
        $role = Role::query()->create([
            'role_name' => 'Student',
            'role_abbrev' => 'STD',
        ]);

        $user = User::factory()->create([
            'username' => 'activeuser01',
            'active' => 1,
            'role_id' => $role->id,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post(route('login.attempt'), [
            'username' => $user->username,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('default'));
        $this->assertAuthenticatedAs($user);
    }
}
