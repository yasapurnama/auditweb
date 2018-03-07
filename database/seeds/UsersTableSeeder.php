<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "Administrator",
            'email' => "admin123@gmail.com",
            'username' => "admin",
            'password' => bcrypt("abc12345"),
            'role' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'name' => "Purnma Yasa",
            'email' => "yasapurnama@gmail.com",
            'username' => "yasapurnama",
            'password' => bcrypt("abc12345"),
            'role' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
