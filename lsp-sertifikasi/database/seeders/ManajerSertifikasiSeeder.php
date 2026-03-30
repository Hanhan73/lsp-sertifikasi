<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
 
class ManajerSertifikasiSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'manajer_sertifikasi@lsp-kap.com'],
            [
                'name'              => 'Manajer Sertifikasi',
                'email'             => 'manajer_sertifikasi@lsp-kap.com',
                'password'          => Hash::make('manajer123!'),  // Ganti setelah login pertama
                'role'              => 'manajer_sertifikasi',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
 
        $this->command->info('Akun manajer sertifikasi berhasil dibuat.');
        $this->command->info('Email   : manajer_sertifikasi@lsp-kap.com');
        $this->command->info('Password: manajer123!');
        $this->command->warn('PENTING: Ganti password setelah login pertama!');
    }
}