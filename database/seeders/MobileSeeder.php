<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MobileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       

        // insert values in property_manager_companies table.
        \DB::statement("INSERT INTO `property_manager_companies` (`name`, `email`, `phone`, `country_code`, `office_contact_no`, `location`, `country_id`, `currency_id`, `status`) VALUES ('sfs', 'sfs@gmail.com', '1234567890', '11', '1234567890', 'mohali', '1', '1', '1');");
        \DB::statement("INSERT INTO `property_manager_companies` (`name`, `email`, `phone`, `country_code`, `office_contact_no`, `location`, `country_id`, `currency_id`, `status`) VALUES ('sfs', 'sfsa@gmail.com', '1234567890', '11', '1234567890', 'address', '1', '1', '1');");

        // insert values in property_managers table.
        \DB::statement("INSERT INTO `property_managers` (`name`, `pm_company_id`, `username`, `country_code`, `email`, `email_verify_code`, `office_contact_no`, `role_id`, `country_id`, `status`, `email_verified_at`, `phone`, `phone_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', '1', 'sfs', '1', 'sfs@gmail.com', '', '1234567890', '1', '1', '1', NULL, '1234567890', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, NULL, NULL);");
        \DB::statement("INSERT INTO `property_managers` (`name`, `pm_company_id`, `username`, `country_code`, `email`, `email_verify_code`, `office_contact_no`, `role_id`, `country_id`, `status`, `email_verified_at`, `phone`, `phone_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', '1', 'sfs', '1', 'sfsa@gmail.com', '', '1234567890', '1', '1', '1', NULL, '1234567890', NULL, 'e10adc3949ba59abbe56e057f20f883e', NULL, NULL, NULL);");

        // insert values in buildings table.
        \DB::statement("INSERT INTO `buildings` (`pm_company_id`, `property_manager_id`, `building_name`, `building_code`, `address`, `units`, `location_link`, `description`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', 'sfs', '1', 'mohali', '1', 'http://sfs.com', 'sfs', '1', NULL, NULL);");

        // insert values in avaliable_units table.
        \DB::statement("INSERT INTO `avaliable_units` (`pm_company_id`, `building_id`, `owner_id`, `unit_no`, `unit_code`, `rooms`, `address`, `bathrooms`, `area_sqm`, `currency_id`, `monthly_rent`, `description`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '1', '1', '1', 'address', '1', '1', '1', '1', 'description', '1', NULL, NULL);");

        // insert values in  privacy_terms table.
        \DB::statement("INSERT INTO `privacy_terms` (`id`, `pm_privacy_policy_en`, `pm_privacy_policy_ar`, `pm_terms_conditions_en`, `pm_terms_conditions_ar`, `app_privacy_policy_en`, `app_privacy_policy_ar`, `app_terms_conditions_en`, `app_terms_conditions_ar`, `created_at`, `updated_at`) VALUES (NULL, 'pm_privacy_policy_en ', 'pm_privacy_policy_ar', 'pm_terms_conditions_en ', 'pm_terms_conditions_ar', 'app_privacy_policy_en', 'app_privacy_policy_ar', 'app_terms_conditions_en', 'app_terms_conditions_ar', NULL, NULL);");

        
    }
}
