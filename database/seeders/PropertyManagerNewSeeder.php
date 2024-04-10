<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\AdminsTableSeeder;
use App\Models\PropertyManager;
use App\Models\PropertyManagerCompany;

use App\Models\AdminModel;
// use App\Models\CountryCurrencyModel;


use Carbon\Carbon;


class PropertyManagerNewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        PropertyManager::create([
            'name' => 'Test manager 1',
            'pm_company_id' => '1' ,
            'username' => 'Test_manager_1',
            'email' => 'test_manager_1@gmail.com',
            'email_verify_code' => '895674',
            'phone' => '12345678900',
            'office_contact_no' => '1234967890',
            'role_id' => '1',
            'country_id' => '3',
            'status' =>  '1' ,
            'email_verified_at' => Carbon::now(),
            'phone_verified_at' => Carbon::now(),
             #password => testing@123# ,
            'password' => 'e10adc3949ba59abbe56e057f20f883e',
            'address' => 'address',
            'latitude' => '25.36853',
            'longitude' => '51.55142',
        ]);

        


      PropertyManager::create([
        'name' => 'Test manager 2',
        'pm_company_id' => 1 ,
        'username' => '',
        'email' => 'test_manager_2@gmail.com',
        'email_verify_code' => '895674',
        'phone' => '12345678901',
        'office_contact_no' => '1234967890',
        'role_id' => '1',
        'country_id' => '1',
        'status' =>  1 ,
        'email_verified_at' => Carbon::now(),
        'phone_verified_at' => Carbon::now(),
         #password => testing@123# ,
        'password' => 'e10adc3949ba59abbe56e057f20f883e',
        'address' => 'address',
        'latitude' => '25.36853', 
        'longitude' => '51.55142',
    ]);

    PropertyManager::create([
        'name' => 'Test manager 3',
        'pm_company_id' => 1 ,
        'username' => '',
        'email' => 'test_manager_3@gmail.com',
        'email_verify_code' => '895674',
        'phone' => '12345678902',
        'office_contact_no' => '1234967890',
        'role_id' => '1',
        'country_id' => '2',
        'status' =>  1 ,
        'email_verified_at' => Carbon::now(),
        'phone_verified_at' => Carbon::now(),
         #password => testing@123# ,
        'password' => '$2a$12$ci3xytWmROkdaKNsmU6xW.0MqRsLDTwpWyTnupU95lg8YWMIw08c.',
        'address' => 'address',
        'latitude' => '25.36853',
        'longitude' => '51.55142',

    ]);

    PropertyManager::create([
        'name' => 'Test manager 4',
        'pm_company_id' => 1 ,
        'username' => '',
        'email' => 'test_manager_4@gmail.com',
        'email_verify_code' => '895674',
        'phone' => '1234567804',
        'office_contact_no' => '1234567890',

        'role_id' => '1',
        'country_id' => '4',
        'status' =>  1 ,
        'email_verified_at' => Carbon::now(),
        'phone_verified_at' => Carbon::now(),
         #password => testing@123# ,
        'password' => 'e10adc3949ba59abbe56e057f20f883e',
        'address' => 'address',
        'latitude' => '25.36853',
        'longitude' => '51.55142',
  ]);

  PropertyManager::create([
    'name' => 'Test manager 5',
    'pm_company_id' => 1 ,
    'username' => '',
    'email' => 'test_manager_5@gmail.com',
    'email_verify_code' => '895674',
    'phone' => '1234567805',
    'office_contact_no' => '1234567890',

    'role_id' => '2',
    'country_id' => '97',
    'status' =>  1 ,
    'email_verified_at' => Carbon::now(),
    'phone_verified_at' => Carbon::now(),
     #password => testing@123# ,
    'password' => 'e10adc3949ba59abbe56e057f20f883e',
    'address' => 'address',
    'latitude' => '25.36853',
    'longitude' => '51.55142',
]);

// PropertyManager::create([
//     'name' => 'Test manager 6',
//     'pm_company_id' => 1 ,
//     'username' => '',
//     'email' => 'test_manager_6@gmail.com',
//     'email_verify_code' => '895674',
//     'phone' => '1234567806',
//     'office_contact_no' => '1234567890',

//     'role_id' => '2',
//     'country_id' => '1',
//     'status' =>  1 ,
//     'email_verified_at' => Carbon::now(),
//     'phone_verified_at' => Carbon::now(),
//      #password => testing@123# ,
//     'password' => 'e10adc3949ba59abbe56e057f20f883e',
//     'address' => 'address',
//     'latitude' => '25.36853',
//     'longitude' => '51.55142',
// ]);


    }
}
