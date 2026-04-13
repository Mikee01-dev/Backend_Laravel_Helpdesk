<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Category::create(['name' => 'Software', 'description' => 'Masalah aplikasi dan OS']);
        Category::create(['name' => 'Hardware', 'description' => 'Kerusakan fisik perangkat']);
        Category::create(['name' => 'Network', 'description' => 'Masalah internet dan WiFi']);

        User::create([
            'name' => 'Michael',
            'username' => 'Michael. Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kirana',
            'username' => 'Kirana. CS',
            'password' => Hash::make('password'),
            'role' => 'helpdesk',
        ]);

        User::create([
            'name' => 'Seto Kurniawan',
            'username' => 'Seto',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
