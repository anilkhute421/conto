<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  0 means property manager.
        //  1 means tenants.
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Privacy Policy', '1', 'en', 'privacy policy', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Privacy Policy', '1', 'ar', 'privacy policy', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Privacy Policy', '0', 'en', 'privacy policy', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Privacy Policy', '0', 'ar', 'privacy policy', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Term & Conditions', '1', 'en', 'Term & Conditions', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Term & Conditions', '1', 'ar', 'Term & Conditions', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Term & Conditions', '0', 'en', 'Term & Conditions', '1', NULL, 2022-01-20 10:19:07);");
        \DB::statement("INSERT INTO `cms_pages` (`id`, `title`, `page_for`, `page_language`, `description`, `status`, `created_at`, `updated_at`) VALUES (NULL, 'Term & Conditions', '0', 'ar', 'Term & Conditions', '1', NULL, 2022-01-20 10:19:07);");

    }
}
