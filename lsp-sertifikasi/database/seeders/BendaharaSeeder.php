<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
 
class BendaharaSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'bendahara@lsp-kap.com'],
            [
                'name'              => 'Bendahara',
                'email'             => 'bendahara@lsp-kap.com',
                'password'          => Hash::make('bendahara123!'),  // Ganti setelah login pertama
                'role'              => 'bendahara',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
 
        $this->command->info('Akun bendahara berhasil dibuat.');
        $this->command->info('Email   : bendahara@lsp-kap.com');
        $this->command->info('Password: bendahara123!');
        $this->command->warn('PENTING: Ganti password setelah login pertama!');
    }
}