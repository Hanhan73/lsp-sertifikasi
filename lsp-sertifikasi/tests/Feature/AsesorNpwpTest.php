<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AsesorNpwpTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): User
    {
        return User::create([
            'name'                => ucfirst($role) . ' Test',
            'email'               => $email,
            'password'            => Hash::make('password'),
            'role'                => $role,
            'is_active'           => true,
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
        ]);
    }

    private function makeAsesor(?User $user = null, array $overrides = []): Asesor
    {
        return Asesor::create(array_merge([
            'nama'          => 'Budi Asesor',
            'nik'           => '3201010101010001',
            'tempat_lahir'  => 'Bandung',
            'tanggal_lahir' => '1985-01-01',
            'jenis_kelamin' => 'L',
            'email'         => 'budi.asesor@example.test',
            'siap_kerja'    => 'Memiliki',
            'status_reg'    => 'aktif',
            'is_active'     => true,
            'user_id'       => $user?->id,
        ], $overrides));
    }

    private function adminPayload(array $overrides = []): array
    {
        return array_merge([
            'nama'          => 'Budi Asesor',
            'nik'           => '3201010101010001',
            'tempat_lahir'  => 'Bandung',
            'tanggal_lahir' => '1985-01-01',
            'jenis_kelamin' => 'L',
            'email'         => 'budi.asesor@example.test',
            'siap_kerja'    => 'Memiliki',
            'status_reg'    => 'aktif',
            'is_active'     => '1',
        ], $overrides);
    }

    private function asesorPayload(array $overrides = []): array
    {
        return array_merge([
            'nama'          => 'Budi Asesor',
            'nik'           => '3201010101010001',
            'tempat_lahir'  => 'Bandung',
            'tanggal_lahir' => '1985-01-01',
            'jenis_kelamin' => 'L',
            'siap_kerja'    => 'Memiliki',
            'status_reg'    => 'aktif',
        ], $overrides);
    }

    // ── Admin ────────────────────────────────────────────────

    public function test_admin_can_create_asesor_with_npwp(): void
    {
        $admin = $this->makeUser('admin', 'admin@example.test');

        $this->actingAs($admin)
            ->post(route('admin.asesors.store'), $this->adminPayload(['npwp' => '12.345.678.9-012.345']))
            ->assertRedirect(route('admin.asesors.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('asesors', [
            'nik'  => '3201010101010001',
            'npwp' => '12.345.678.9-012.345',
        ]);
    }

    public function test_admin_can_update_asesor_npwp(): void
    {
        $admin  = $this->makeUser('admin', 'admin@example.test');
        $asesor = $this->makeAsesor();

        $this->actingAs($admin)
            ->put(route('admin.asesors.update', $asesor), $this->adminPayload(['npwp' => '99.888.777.6-555.444']))
            ->assertRedirect(route('admin.asesors.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('99.888.777.6-555.444', $asesor->fresh()->npwp);
    }

    public function test_admin_sees_npwp_on_edit_form_and_detail(): void
    {
        $admin  = $this->makeUser('admin', 'admin@example.test');
        $asesor = $this->makeAsesor(null, ['npwp' => '11.222.333.4-555.666']);

        $this->actingAs($admin)
            ->get(route('admin.asesors.edit', $asesor))
            ->assertOk()
            ->assertSee('name="npwp"', false)
            ->assertSee('11.222.333.4-555.666');

        $response = $this->actingAs($admin)
            ->getJson(route('admin.asesors.show', $asesor))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertStringContainsString('NPWP', $response->json('html'));
        $this->assertStringContainsString('11.222.333.4-555.666', $response->json('html'));
    }

    public function test_npwp_is_optional_and_length_limited_for_admin(): void
    {
        $admin  = $this->makeUser('admin', 'admin@example.test');
        $asesor = $this->makeAsesor(null, ['npwp' => '11.222.333.4-555.666']);

        $this->actingAs($admin)
            ->put(route('admin.asesors.update', $asesor), $this->adminPayload(['npwp' => str_repeat('9', 31)]))
            ->assertSessionHasErrors('npwp');

        $this->actingAs($admin)
            ->put(route('admin.asesors.update', $asesor), $this->adminPayload(['npwp' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull($asesor->fresh()->npwp);
    }

    // ── Asesor (self-service) ────────────────────────────────

    public function test_asesor_sees_own_npwp_on_profile(): void
    {
        $user = $this->makeUser('asesor', 'budi.asesor@example.test');
        $this->makeAsesor($user, ['npwp' => '11.222.333.4-555.666']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('NPWP')
            ->assertSee('name="npwp"', false)
            ->assertSee('11.222.333.4-555.666');
    }

    public function test_asesor_can_update_own_npwp(): void
    {
        $user   = $this->makeUser('asesor', 'budi.asesor@example.test');
        $asesor = $this->makeAsesor($user);

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('profile.update-asesor-data'), $this->asesorPayload(['npwp' => '12.345.678.9-012.345']))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasNoErrors();

        $this->assertSame('12.345.678.9-012.345', $asesor->fresh()->npwp);
    }

    public function test_asesor_npwp_validation(): void
    {
        $user   = $this->makeUser('asesor', 'budi.asesor@example.test');
        $asesor = $this->makeAsesor($user, ['npwp' => '11.222.333.4-555.666']);

        $this->actingAs($user)
            ->put(route('profile.update-asesor-data'), $this->asesorPayload(['npwp' => str_repeat('9', 31)]))
            ->assertSessionHasErrors('npwp');

        $this->assertSame('11.222.333.4-555.666', $asesor->fresh()->npwp);
    }

    public function test_asesor_update_only_changes_own_record(): void
    {
        $user  = $this->makeUser('asesor', 'budi.asesor@example.test');
        $mine  = $this->makeAsesor($user);
        $other = $this->makeAsesor(null, [
            'nama'  => 'Asesor Lain',
            'nik'   => '3201010101010002',
            'email' => 'lain@example.test',
            'npwp'  => '00.000.000.0-000.000',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update-asesor-data'), $this->asesorPayload(['npwp' => '12.345.678.9-012.345']))
            ->assertSessionHasNoErrors();

        $this->assertSame('12.345.678.9-012.345', $mine->fresh()->npwp);
        $this->assertSame('00.000.000.0-000.000', $other->fresh()->npwp);
    }

    public function test_non_admin_cannot_edit_asesor_via_admin_route(): void
    {
        $user   = $this->makeUser('asesor', 'budi.asesor@example.test');
        $asesor = $this->makeAsesor($user, ['npwp' => '11.222.333.4-555.666']);

        $this->actingAs($user)
            ->put(route('admin.asesors.update', $asesor), $this->adminPayload(['npwp' => '99.999.999.9-999.999']));

        $this->assertSame('11.222.333.4-555.666', $asesor->fresh()->npwp);
    }
}
