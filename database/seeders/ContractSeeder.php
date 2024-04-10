<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContractModel;


class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ContractModel::create([
            'name' => 'contract1',
            'pm_company_id' => '1',
            'Tenant_id' => '1',
            'building_id' => '1',
            'unit_id' => '50',
            'start_date' => '1000-01-01 00:00:00', 
            'end_date' => '9999-12-31 23:59:59',
            'status' => '1'

        ]);

        ContractModel::create([
                'name' => 'contract2',
                'pm_company_id' => '13',
                'Tenant_id' => '2',
                'building_id' => '2',
                'unit_id' => '58',
                'start_date' => '1000-01-01 00:00:00', 
                'end_date' => '9999-12-31 23:59:59',
                'status' => '1'

            ]);   
            
        ContractModel::create([
                'name' => 'contract3',
                'pm_company_id' => '7',
                'Tenant_id' => '3',
                'building_id' => '3',
                'unit_id' => '56',
                'start_date' => '1000-01-01 00:00:00', 
                'end_date' => '9999-12-31 23:59:59',
                'status' => '1'

            ]);  

        ContractModel::create([
                'name' => 'contract4',
                'pm_company_id' => '8',
                'Tenant_id' => '4',
                'building_id' => '4',
                'unit_id' => '57',
                'start_date' => '1000-01-01 00:00:00', 
                'end_date' => '9999-12-31 23:59:59',
                'status' => '1'

            ]);  

        ContractModel::create([
                'name' => 'contract5',
                'pm_company_id' => '9',
                'Tenant_id' => '5',
                'building_id' => '5',
                'unit_id' => '60',
                'start_date' => '1000-01-01 00:00:00', 
                'end_date' => '9999-12-31 23:59:59',
                'status' => '1'
            ]);  
    }
}
