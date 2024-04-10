<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminModel;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminModel::create([
            'name' => 'Admin',
            'email' => 'mohammed@contolio.com',
            'phone' => '1234567890',
            'username' => 'Admin',
            'password' => '$2y$10$l5O8eSngcQGXWIWJ1n4Zw.t8gwFW4iFbCB6F/dDdES/Gbry/i67Gy',
            'email_verify_code' => ''
        ]);
    }
}
