<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'user_id' => 1,
            'sendmail' => true,
            'notify' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('settings')->insert([
            'user_id' => 2,
            'sendmail' => true,
            'notify' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
