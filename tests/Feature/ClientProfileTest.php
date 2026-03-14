<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Tenant,
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

    public function test_owner_can_update_invoice_number_format(): void
    {
        Storage::fake('public');

        $user = User::factory()->owner()->create();

        $this->actingAs($user)
            ->patch(route('client.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'invoice_number_format' => 'INV-{year}-{property_unit_code}-{num}',
                'invoice_sender_name' => 'SIA MBC Solutions',
                'invoice_sender_address' => 'Stacijas iela 8-6, Grobiņa, LV-3430',
                'invoice_footer_text' => 'Rēķins sagatavots elektroniski.',
                'invoice_payment_terms_text' => '7 dienas. Pārskaitījums.',
                'invoice_vat_enabled' => '1',
                'invoice_vat_rate' => '21',
                'invoice_logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('client.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'invoice_number_format' => 'INV-{year}-{property_unit_code}-{num}',
            'invoice_sender_name' => 'SIA MBC Solutions',
            'invoice_footer_text' => 'Rēķins sagatavots elektroniski.',
            'invoice_vat_enabled' => 1,
        ]);

        $this->assertNotNull($user->fresh()->invoice_logo_path);
        Storage::disk('public')->assertExists($user->fresh()->invoice_logo_path);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Tenant,
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
            'role' => UserRole::Tenant,
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
