<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@pqrs.com',
            'password' => Hash::make('Admin1234'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Agente 1',
            'email' => 'agente1@pqrs.com',
            'password' => Hash::make('Agente1234'),
            'role' => 'agente',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Agente 2',
            'email' => 'agente2@pqrs.com',
            'password' => Hash::make('Agente1234'),
            'role' => 'agente',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@pqrs.com',
            'password' => Hash::make('Cliente1234'),
            'role' => 'cliente',
            'is_active' => true,
        ]);
    }
}
