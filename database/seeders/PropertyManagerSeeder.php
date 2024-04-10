<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PropertyManager;
use App\Models\PropertyManagerCompany;

use App\Models\AdminModel;
// use App\Models\CountryCurrencyModel;


use Carbon\Carbon;


class PropertyManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // AdminModel::create([
        //     'name' => 'Admin',
        //     'email' => 'mohammed@contolio.com',
        //     'phone' => '1234567890',
        //     'username' => 'Admin',
        //     'password' => '$2y$10$l5O8eSngcQGXWIWJ1n4Zw.t8gwFW4iFbCB6F/dDdES/Gbry/i67Gy',
        //     'email_verify_code' => ''
        // ]);

       

        PropertyManagerCompany::create([
            'name' => 'Test Company',
            'email' => 'test_comp@gmail.com',
            'phone' => '1234567890',
             //'department' => 'hr',
            'country_code' => '91',
            'office_contact_no' => '9996379155',
            'location' => 'testing ',
            'country_id' => '1',
            'currency_id' => '10' ,
            'status' => 1
        ]);

    //     PropertyManager::create([
    //         'name' => 'Test manager 1',
    //         'pm_company_id' => 1 ,
    //         'username' => '',
    //         'email' => 'test_manager_1@gmail.com',
    //         'email_verify_code' => '895674',
    //         'phone' => '1234567990',
    //         'office_contact_no' => '1234567890',
    //         'role_id' => '1',
    //         // 'country_id' => '1',
    //         'status' =>  1 ,
    //         'email_verified_at' => Carbon::now(),
    //         'phone_verified_at' => Carbon::now(),
    //          #password => testing@123# ,
    //         'password' => '$2a$12$ci3xytWmROkdaKNsmU6xW.0MqRsLDTwpWyTnupU95lg8YWMIw08c.',
    //     'latitude' => '12.3',
    //     'longitude' => '32.4'

    //   ]);


      PropertyManager::create([
        'name' => 'Test manager 2',
        'pm_company_id' => 1 ,
        'username' => '',
        'email' => 'test_manager_2@gmail.com',
        'email_verify_code' => '895674',
        'phone' => '1234567890',
        'office_contact_no' => '1234567890',
        'role_id' => '1',
        // 'country_id' => '1',
        'status' =>  1 ,
        'email_verified_at' => Carbon::now(),
        'phone_verified_at' => Carbon::now(),
         #password => testing@123# ,
        'password' => '$2a$12$ci3xytWmROkdaKNsmU6xW.0MqRsLDTwpWyTnupU95lg8YWMIw08c.',
        'latitude' => '12.3',
        'longitude' => '42.2'
    ]);

    }
}
