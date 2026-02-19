<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin@kpspams.test',
        ], [
            'name' => 'Gunadi Admin',
            'phone_number' => '082269245660',
            'role' => User::ROLE_ADMIN,
            'password' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'petugas@kpspams.test',
        ], [
            'name' => 'Petugas Operator',
            'phone_number' => '082111223344',
            'role' => User::ROLE_PETUGAS,
            'password' => 'password123',
        ]);
    }
}
