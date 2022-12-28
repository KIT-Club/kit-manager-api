<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username' => 'AT010101',
            'name' => 'Test User',
            'birthday' => '2000-01-01',
            'class' => 'AT1A',
            'major' => "Công nghệ thông tin"
        ]);
    }
}
