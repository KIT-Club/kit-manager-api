<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Committee;
use App\Models\UserRole;
use App\Models\UserCommittee;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            User::create([
                'username' => 'AT010101',
                'name' => 'Nguyen Van A',
                'birthday' => '1986-01-01',
                'class' => 'AT1A',
                'major' => "An Toàn thông tin"
            ]);

            Role::create(['name' => 'Super Admin']);
            Role::create(['name' => 'Owner']);
            Role::create(['name' => 'Moderator']);
            Role::create(['name' => 'Member']);

            Committee::create(['name' => "Điều Hành"]);
            Committee::create(['name' => "Chuyên Môn"]);
            Committee::create(['name' => "Truyền Thông"]);
            Committee::create(['name' => "Hậu Cần"]);

            DB::table('user_role')->create([
                'user_id' => User::where('username', 'AT010101')->first()->id,
                'role_id' => Role::where('name', "Super Admin")->first()->id,
            ]);

            DB::table('user_committee')->create([
                'user_id' => User::where('username', 'AT010101')->first()->id,
                'committee_id' => Committee::where('name', "Điều Hành")->first()->id,
            ]);
        });
    }
}
