<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuildingModel;


class BuldingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        BuildingModel::create([
            'pm_company_id' => '1',
            'property_manager_id' => '22' ,
            'building_name' => 'building_1',
            'building_code' => '1XPVKT14',
            'address' => 'Sydney NSW, Australia',
            'units' => '0',
            'location_link' => 'https://www.google.com/maps/place',
            'description' => 'Lorem ipsajwbghhh',
            'status' => '1',
            
        ]);

        BuildingModel::create([
            'pm_company_id' => '2',
            'property_manager_id' => '23' ,
            'building_name' => 'building_2',
            'building_code' => '1XPVKT14',
            'address' => 'Sydney NSW, Australia',
            'units' => '0',
            'location_link' => 'https://www.google.com/maps/place',
            'description' => 'Lorem ipsajwbghhh',
            'status' => '1',
            
        ]);

        BuildingModel::create([
            'pm_company_id' => '3',
            'property_manager_id' => '24' ,
            'building_name' => 'building_3',
            'building_code' => '1XPVKT14',
            'address' => 'Sydney NSW, Australia',
            'units' => '0',
            'location_link' => 'https://www.google.com/maps/place',
            'description' => 'Lorem ipsajwbghhh',
            'status' => '1',
            
        ]);

        BuildingModel::create([
            'pm_company_id' => '4',
            'property_manager_id' => '25' ,
            'building_name' => 'building_4',
            'building_code' => '1XPVKT14',
            'address' => 'Sydney NSW, Australia',
            'units' => '0',
            'location_link' => 'https://www.google.com/maps/place',
            'description' => 'Lorem ipsajwbghhh',
            'status' => '1',
            
        ]);

        BuildingModel::create([
            'pm_company_id' => '5',
            'property_manager_id' => '26' ,
            'building_name' => 'building_5',
            'building_code' => '1XPVKT14',
            'address' => 'Sydney NSW, Australia',
            'units' => '0',
            'location_link' => 'https://www.google.com/maps/place',
            'description' => 'Lorem ipsajwbghhh',
            'status' => '1',
            
        ]);


    }
}
