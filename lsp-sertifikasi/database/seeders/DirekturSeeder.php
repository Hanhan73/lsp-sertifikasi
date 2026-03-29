<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
 
class DirekturSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'direktur@lsp-kap.com'],
            [
                'name'              => 'Drs. HM. Jamil Latief, MM., M.Pd.',
                'email'             => 'direktur@lsp-kap.com',
                'password'          => Hash::make('direktur123!'),  // Ganti setelah login pertama
                'role'              => 'direktur',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
 
        $this->command->info('Akun direktur berhasil dibuat.');
        $this->command->info('Email   : direktur@lsp-kap.com');
        $this->command->info('Password: direktur123!');
        $this->command->warn('PENTING: Ganti password setelah login pertama!');
    }
}