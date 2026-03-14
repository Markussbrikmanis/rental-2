<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list_and_edit_user(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = SubscriptionPlan::factory()->create();
        $user = User::factory()->tenant()->create([
            'name' => 'Tenant Person',
            'email' => 'tenant@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('client.admin.users.index'))
            ->assertOk()
            ->assertSee('Tenant Person');

        $this->actingAs($admin)
            ->put(route('client.admin.users.update', $user), [
                'name' => 'Owner Person',
                'email' => 'owner-converted@example.com',
                'role' => UserRole::Owner->value,
                'subscription_plan_id' => $plan->id,
                'owner_trial_ends_at' => '2026-04-01',
            ])
            ->assertRedirect(route('client.admin.users.index'));

        $user->refresh();

        $this->assertSame(UserRole::Owner, $user->role);
        $this->assertSame($plan->id, $user->subscription_plan_id);
        $this->assertSame('2026-04-01', $user->owner_trial_ends_at?->format('Y-m-d'));
    }

    public function test_admin_can_delete_other_user_but_not_self(): void
    {
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->tenant()->create();

        $this->actingAs($admin)
            ->delete(route('client.admin.users.destroy', $otherUser))
            ->assertRedirect(route('client.admin.users.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('client.admin.users.destroy', $admin))
            ->assertRedirect(route('client.admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_can_send_password_reset_link(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->tenant()->create([
            'email' => 'reset-me@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('client.admin.users.send-password-reset', $user))
            ->assertRedirect(route('client.admin.users.index'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_non_admin_cannot_access_admin_user_management(): void
    {
        $owner = User::factory()->owner()->create();
        $user = User::factory()->tenant()->create();

        $this->actingAs($owner)
            ->get(route('client.admin.users.index'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('client.admin.users.edit', $user))
            ->assertForbidden();
    }
}
