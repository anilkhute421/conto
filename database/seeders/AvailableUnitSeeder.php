<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailableUnitModel;


class AvailableUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        AvailableUnitModel::create([
            'pm_company_id' => '1',
            'building_id' => '2',
            'owner_id' => '1',
            'unit_no' => '1',
            'unit_code' => '1',
            'rooms' => 1,
            'address' => 'address',
            'bathrooms' => '1',
            'area_sqm' => '1',
            'currency_id' => '1',
            'monthly_rent' => '1',
            'description' => 'description',
            'status' => '1',
           ]);


           AvailableUnitModel::create([
            'pm_company_id' => '1',
            'building_id' => '1',
            'owner_id' => '1',
            'unit_no' => '2',
            'unit_code' => '1',
            'rooms' => 1,
            'address' => 'address',
            'bathrooms' => '1',
            'area_sqm' => '1',
            'currency_id' => '1',
            'monthly_rent' => '1',
            'description' => 'description',
            'status' => '1',
           ]);

           AvailableUnitModel::create([
            'pm_company_id' => '1',
            'building_id' => '4',
            'owner_id' => '1',
            'unit_no' => '3',
            'unit_code' => '1',
            'rooms' => 1,
            'address' => 'address',
            'bathrooms' => '1',
            'area_sqm' => '1',
            'currency_id' => '1',
            'monthly_rent' => '1',
            'description' => 'description',
            'status' => '1',
           ]);

           AvailableUnitModel::create([
            'pm_company_id' => '1',
            'building_id' => '5',
            'owner_id' => '1',
            'unit_no' => '4',
            'unit_code' => '1',
            'rooms' => 1,
            'address' => 'address',
            'bathrooms' => '1',
            'area_sqm' => '1',
            'currency_id' => '1',
            'monthly_rent' => '1',
            'description' => 'description',
            'status' => '1',
           ]);

           AvailableUnitModel::create([
            'pm_company_id' => '1',
            'building_id' => '6',
            'owner_id' => '1',
            'unit_no' => '5',
            'unit_code' => '1',
            'rooms' => 1,
            'address' => 'address',
            'bathrooms' => '1',
            'area_sqm' => '1',
            'currency_id' => '1',
            'monthly_rent' => '1',
            'description' => 'description',
            'status' => '1',
           ]);
    }
}
