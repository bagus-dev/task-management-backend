<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            [
                'name'      => 'Admin',
                'email'     => 'admin@gmail.com',
                'password'  => bcrypt('admin'),
                'role'      => 1, // 1 = Admin, 2 = User
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name'      => 'Bagus Puji Rahardjo',
                'email'     => 'user@gmail.com',
                'password'  => bcrypt('password'),
                'role'      => 2, // 1 = Admin, 2 = User
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
