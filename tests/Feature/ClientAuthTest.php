<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_client_auth_pages(): void
    {
        $this->get('/client/login')->assertOk();
        $this->get('/client/register')->assertOk();
    }

    public function test_guest_is_redirected_to_client_login_when_accessing_panel(): void
    {
        $this->get('/client/panel')
            ->assertRedirect('/client/login');
    }

    public function test_user_can_register_as_owner_or_tenant_and_is_logged_in(): void
    {
        $response = $this->post('/client/register', [
            'name' => 'Owner Person',
            'email' => 'owner-person@example.com',
            'role' => UserRole::Owner->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('client.panel'));
        $this->assertAuthenticated();

        $user = User::firstWhere('email', 'owner-person@example.com');

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Owner, $user->role);
    }

    public function test_admin_role_cannot_be_self_registered(): void
    {
        $response = $this->from('/client/register')->post('/client/register', [
            'name' => 'Bad Admin Attempt',
            'email' => 'bad-admin@example.com',
            'role' => UserRole::Admin->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect('/client/register')
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'bad-admin@example.com',
        ]);
    }

    public function test_client_panel_renders_for_each_supported_role(): void
    {
        $expectations = [
            UserRole::Admin->value => 'Administrators',
            UserRole::Owner->value => 'Saņemts šomēnes',
            UserRole::Tenant->value => 'Īrnieks',
        ];

        foreach ($expectations as $role => $text) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->get('/client/panel')
                ->assertOk()
                ->assertSee($text);

            auth()->logout();
        }
    }
}
