<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Owner,
        ]);

        $this->actingAs($user)
            ->get(route('client.profile.edit'))
            ->assertOk()
            ->assertSee('Profila iestatījumi');
    }

    public function test_authenticated_user_can_update_profile_details(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Tenant,
        ]);

        $this->actingAs($user)
            ->patch(route('client.profile.update'), [
                'name' => 'Jauns Vārds',
                'email' => 'jauns@example.com',
            ])
            ->assertRedirect(route('client.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jauns Vārds',
            'email' => 'jauns@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Owner,
        ]);

        $this->actingAs($user)
            ->put(route('client.profile.password'), [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('client.profile.edit'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_authenticated_user_can_delete_account(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Owner,
        ]);

        $this->actingAs($user)
            ->delete(route('client.profile.destroy'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('client.login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
