<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    

        \DB::statement("INSERT INTO `roles` (`id`, `role_title`, `buildings_management_create`, `buildings_management_view`, `buildings_management_edit`, `buildings_management_delete`, `contracts_management_create`, `contracts_management_view`, `contracts_management_edit`, `contracts_management_delete`, `payment_management_create`, `payment_management_view`, `payment_management_edit`, `payment_management_delete`, `tenant_management_create`, `tenant_management_view`, `tenant_management_edit`, `tenant_management_delete`, `units_management_create`, `units_management_view`, `units_management_edit`, `units_management_delete`, `created_at`, `updated_at`) VALUES (NULL,'admin', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', NULL, NULL);");
       
        \DB::statement("INSERT INTO `roles` (`id`, `role_title`, `buildings_management_create`, `buildings_management_view`, `buildings_management_edit`, `buildings_management_delete`, `contracts_management_create`, `contracts_management_view`, `contracts_management_edit`, `contracts_management_delete`, `payment_management_create`, `payment_management_view`, `payment_management_edit`, `payment_management_delete`, `tenant_management_create`, `tenant_management_view`, `tenant_management_edit`, `tenant_management_delete`, `units_management_create`, `units_management_view`, `units_management_edit`, `units_management_delete`, `created_at`, `updated_at`) VALUES (NULL, 'viewer', '0', '1', '0', '0', '0', '1', '0', '0', '0', '1', '0', '0', '0', '1', '0', '0', '0', '1', '0', '0', NULL, NULL);");


        }
}