<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // insert values in  tenants table.

        \DB::statement("INSERT INTO `tenants` (`id`, `first_name`, `last_name`, `property_manager_id`, `building_id`, `email`, `unique_email_key`, `email_key_expire`, `unit_id`, `phone`, `country_code`, `country_id`, `language`, `password`, `address`, `os_type`, `os_version`, `device_token`, `app_version`, `created_at`, `updated_at`, `otp`, `status`,`is_email_verify`,`is_phone_verify`,`email_url_key`) VALUES (NULL, 'first_name', 'last_name', '1', '1', 'email1@gmail.com', '', '', '1', '2345678901', '91', '1', '', '$2y$10\$osF1uUT17uo2hJEFucmh9e.o/y.sWNWcQKvr1k37JlK5yeWqU5/YG', 'address', '123', NULL, NULL, NULL, NULL, NULL, '', '1','1','1',Null);");
        \DB::statement("INSERT INTO `tenants` (`id`, `first_name`, `last_name`, `property_manager_id`, `building_id`, `email`, `unique_email_key`, `email_key_expire`, `unit_id`, `phone`, `country_code`, `country_id`, `language`, `password`, `address`, `os_type`, `os_version`, `device_token`, `app_version`, `created_at`, `updated_at`, `otp`, `status`,`is_email_verify`,`is_phone_verify`,`email_url_key`) VALUES (NULL, 'first_name', 'last_name', '1', '1', 'email2@gmail.com', '', '', '1', '3456785622', '91', '1', '', '$2y$10\$osF1uUT17uo2hJEFucmh9e.o/y.sWNWcQKvr1k37JlK5yeWqU5/YG', 'address', '123', NULL, NULL, NULL, NULL, NULL, '', '1','1','1',Null);");
        \DB::statement("INSERT INTO `tenants` (`id`, `first_name`, `last_name`, `property_manager_id`, `building_id`, `email`, `unique_email_key`, `email_key_expire`, `unit_id`, `phone`, `country_code`, `country_id`, `language`, `password`, `address`, `os_type`, `os_version`, `device_token`, `app_version`, `created_at`, `updated_at`, `otp`, `status`,`is_email_verify`,`is_phone_verify`,`email_url_key`) VALUES (NULL, 'first_name', 'last_name', '1', '1', 'email3@gmail.com', '', '', '1', '2222222222', '91', '1', '', '$2y$10\$osF1uUT17uo2hJEFucmh9e.o/y.sWNWcQKvr1k37JlK5yeWqU5/YG', 'address', '123', NULL, NULL, NULL, NULL, NULL, '', '1','1','1',Null);");
        \DB::statement("INSERT INTO `tenants` (`id`, `first_name`, `last_name`, `property_manager_id`, `building_id`, `email`, `unique_email_key`, `email_key_expire`, `unit_id`, `phone`, `country_code`, `country_id`, `language`, `password`, `address`, `os_type`, `os_version`, `device_token`, `app_version`, `created_at`, `updated_at`, `otp`, `status`,`is_email_verify`,`is_phone_verify`,`email_url_key`) VALUES (NULL, 'first_name', 'last_name', '1', '1', 'email4@gmail.com', '', '', '1', '1111111111', '91', '1', '', '$2y$10\$osF1uUT17uo2hJEFucmh9e.o/y.sWNWcQKvr1k37JlK5yeWqU5/YG', 'address', '123', NULL, NULL, NULL, NULL, NULL, '', '1','1','1',Null);");
        \DB::statement("INSERT INTO `tenants` (`id`, `first_name`, `last_name`, `property_manager_id`, `building_id`, `email`, `unique_email_key`, `email_key_expire`, `unit_id`, `phone`, `country_code`, `country_id`, `language`, `password`, `address`, `os_type`, `os_version`, `device_token`, `app_version`, `created_at`, `updated_at`, `otp`, `status`,`is_email_verify`,`is_phone_verify`,`email_url_key`) VALUES (NULL, 'first_name', 'last_name', '1', '1', 'email5@gmail.com', '', '', '1', '3333333332', '91', '1', '', '$2y$10\$osF1uUT17uo2hJEFucmh9e.o/y.sWNWcQKvr1k37JlK5yeWqU5/YG', 'address', '123', NULL, NULL, NULL, NULL, NULL, '', '1','1','1',Null);");

    }
}
