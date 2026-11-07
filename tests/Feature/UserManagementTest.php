<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kelola akun admin di Pengaturan → Akun & Keamanan (pengganti resource
 * Filament yang sudah dilepas).
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_admin_baru_bisa_dibuat_dengan_password_ter_hash(): void
    {
        $this->post(route('pengaturan.akun.store'), [
            'name' => 'Admin Kedua',
            'email' => 'admin2@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user = User::where('email', 'admin2@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_password_minimal_8_karakter_dan_harus_dikonfirmasi(): void
    {
        $this->post(route('pengaturan.akun.store'), [
            'name' => 'Admin Kedua',
            'email' => 'admin2@example.com',
            'password' => 'pendek',
            'password_confirmation' => 'lain',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'admin2@example.com']);
    }

    public function test_ganti_password_mengganti_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-lama')]);

        $this->put(route('pengaturan.akun.password', $user), [
            'password' => 'password-baru123',
            'password_confirmation' => 'password-baru123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru123', $user->refresh()->password));
    }

    public function test_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $this->delete(route('pengaturan.akun.destroy', $this->admin))->assertSessionHasErrors('akun');

        $this->assertModelExists($this->admin);
    }

    public function test_bisa_menghapus_akun_admin_lain(): void
    {
        $other = User::factory()->create();

        $this->delete(route('pengaturan.akun.destroy', $other))->assertSessionHasNoErrors();

        $this->assertModelMissing($other);
    }
}
