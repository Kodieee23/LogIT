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
        User::create([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'username' => 'staff',
            'full_name' => 'Staff User',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        // Seed initial categories
        $categories = ['Hardware', 'Software', 'Network', 'Account Access'];
        foreach ($categories as $cat) {
            \App\Models\Category::create(['name' => $cat]);
        }
    }
}
