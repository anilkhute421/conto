<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PhpParser\Node\Expr\Cast\Object_;

Route::post('v1/admin/login', [AdminController::class,'admin_login']);//access token
Route::post('v1/admin/admin_forgot_password', [AdminController::class,'admin_forgot_password']);

Route::group(['prefix' => 'v1/admin', 'middleware' => ['auth:sanctum', 'abilities:admin' ]], function(){

        Route::get('check_access_token', [AdminController::class,'check_access_token']);
        Route::post('check_access_token_otp', [AdminController::class,'check_access_token_otp']);
        Route::post('verify_otp', [AdminController::class,'verify_otp']);
        Route::post('change_password', [AdminController::class,'change_password']);
        Route::get('logout', [AdminController::class,'logout']);


        Route::get('get_profile', [AdminController::class,'get_profile']);
        Route::post('update_contact_info', [AdminController::class,'update_contact_info']);

        Route::get('company_dropdown', [AdminController::class,'company_dropdown']);
        Route::get('roles_dropdown', [AdminController::class,'roles_dropdown']);
        Route::get('country_dropdown', [AdminController::class,'country_dropdown']);
        Route::get('currency_dropdown', [AdminController::class,'currency_dropdown']);

        Route::post('buildings_dropdown', [AdminController::class,'buildings_dropdown']);
        Route::post('units_dropdown', [AdminController::class,'units_dropdown']);
        Route::post('company_by_country_dropdown', [AdminController::class,'company_by_country_dropdown']);


        //companies
        Route::get('property_manager_company', [AdminController::class,'property_manager_company']);
        Route::post('add_property_manager_company', [AdminController::class,'add_property_manager_company']);
        Route::post('view_property_manager_company', [AdminController::class,'view_property_manager_company']);
        Route::post('edit_property_manager_company', [AdminController::class,'edit_property_manager_company']);
        Route::post('PM_list_by_company', [AdminController::class,'PM_list_by_company']);

        Route::post('pm_company_active_deactive', [AdminController::class,'pm_company_active_deactive']);


        //property_managers
        Route::get('property_manager_users', [AdminController::class,'property_manager_users']);
        Route::post('add_property_manager', [AdminController::class,'add_property_manager']);
        Route::post('view_property_managers', [AdminController::class,'view_property_managers']);
        Route::post('pm_active_deactive', [AdminController::class,'pm_active_deactive']);
        Route::post('update_pm_password', [AdminController::class,'update_pm_password']);
        Route::post('edit_property_manager', [AdminController::class,'edit_property_manager']);


        //tenants
        Route::get('tenants', [AdminController::class,'tenants']);
        Route::post('view_tenants', [AdminController::class,'view_tenants']);
        Route::post('update_tenant_password', [AdminController::class,'update_tenant_password']);
        Route::post('edit_tenant', [AdminController::class,'edit_tenant']);

        //buildings
        Route::get('host_units', [AdminTwoController::class,'host_units']);
        Route::post('view_host_units', [AdminTwoController::class,'view_host_units']);

        //contact
        Route::get('contact_request', [AdminTwoController::class,'contact_request']);
        Route::post('view_contact_request', [AdminTwoController::class,'view_contact_request']);

        //cms page
        Route::get('manage_cms', [AdminTwoController::class,'manage_cms']);
        Route::get('cms_page_for_dropdown', [AdminTwoController::class,'cms_page_for_dropdown']);
        Route::post('edit_manage_cms', [AdminTwoController::class,'edit_manage_cms']);
        Route::post('view_cms', [AdminTwoController::class,'view_cms']);

        //roles
        Route::get('role_list', [AdminTwoController::class,'role_list']);
        Route::post('create_role', [AdminTwoController::class,'create_role']);
        Route::post('view_role', [AdminTwoController::class,'view_role']);
        Route::post('update_role', [AdminTwoController::class,'update_role']);

        //pm notifications
        Route::get('pm_notification_list', [AdminTwoController::class,'pm_notification_list']);
        Route::get('pm_drop_down_at_admin_notification', [AdminTwoController::class,'pm_drop_down_at_admin_notification']);
        Route::post('add_pm_notification', [AdminTwoController::class,'add_pm_notification']);
        Route::post('view_pm_notification', [AdminTwoController::class,'view_pm_notification']);
        Route::post('delete_pm_notification', [AdminTwoController::class,'delete_pm_notification']);


        //tenant notifications
        Route::get('tenant_notification_list', [AdminTwoController::class,'tenant_notification_list']);
        Route::get('tenant_drop_down_at_admin_notification', [AdminTwoController::class,'tenant_drop_down_at_admin_notification']);
        Route::post('add_tenant_notification', [AdminTwoController::class,'add_tenant_notification']);//send_notification
        Route::post('view_tenant_notification', [AdminTwoController::class,'view_tenant_notification']);
        Route::post('delete_tenant_notification', [AdminTwoController::class,'delete_tenant_notification']);

        Route::post('admin_update', [MobileApiController::class,'admin_update']);





});//auth admin




// Route::get('v1/admin/test_admin', function(){
//     \Log::debug('get v1/test_admin');
// });

// Route::post('v1/admin/test_admin_post', function(){
//     \Log::debug('post v1/admin/test_admin_post');
//     $all_headers = apache_request_headers();
//     \Log::debug($all_headers);
//     // dd($all_headers);
// });
