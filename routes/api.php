<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('v1/property_manager_login', [PropertyManagerTwoController::class,'login']);//access token
Route::post('v1/verify_otp', [PropertyManagerTwoController::class,'verify_otp']);//access token
Route::post('v1/upload_image', [PropertyManagerTwoController::class,'upload_image']);//access token
Route::post('v1/forgot_password_email', [PropertyManagerTwoController::class,'forgot_password_email']);//access token


Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:property_manager' , 'headerlang']], function(){

    //PM
    Route::get('check_pm_access_token', [BuildingManageController::class,'check_pm_access_token']);

    Route::get('property_manager_profile', [PropertyManagerController::class,'property_manager_profile']);
    Route::post('update_pm_profile', [PropertyManagerController::class,'update_pm_profile']);
    Route::post('change_password', [PropertyManagerController::class,'change_password']);
    Route::get('view_pm_profile/{id}', [PropertyManagerController::class,'view_pm_profile']);

    //others for PM
    Route::get('get_roles', [PropertyManagerController::class,'get_roles']);
    Route::get('get_all_companies', [PropertyManagerController::class,'get_all_companies']);
    Route::get('get_all_currency', [BuildingManageController::class,'get_all_currency']);
    Route::get('get_tenant_list/{building_id}', [BuildingManageController::class,'get_tenant_list']);


    //buildings
    Route::post('building_listing', [BuildingManageController::class,'building_listing']);
    Route::post('search_building', [BuildingManageController::class,'search_building']);
    Route::post('add_building', [BuildingManageController::class,'add_building']);
    Route::post('update_building', [BuildingManageController::class,'update_building']);
    Route::get('view_building/{id}', [BuildingManageController::class,'view_building']);
    Route::get('get_all_buildings/{pm_id}/', [BuildingManageController::class,'get_all_buildings']);//drop down
    Route::post('activate_deactivate_building', [BuildingManageController::class,'activate_deactivate_building']);
    Route::post('building_delete_by_building_id', [BuildingThreeController::class,'building_delete_by_building_id']);



     //owner
     Route::post('search_owner_list', [BuildingManageController::class,'search_owner_list']);
     Route::post('owner_listing', [BuildingManageController::class,'owner_listing']);
     Route::post('add_owner', [BuildingManageController::class,'add_owner']);
     Route::post('edit_owner', [BuildingManageController::class,'edit_owner']);
     Route::get('owner_details/{owner_id}', [BuildingManageController::class,'owner_details']);
     Route::get('delete_owner/{owner_id}', [BuildingManageController::class,'delete_owner']);
     Route::get('get_all_owners', [BuildingManageController::class,'get_all_owners']);

     Route::get('get_currency_code_by_building_id/{building_id}', [BuildingManageController::class,'get_currency_code_by_building_id']);

    //available units
    Route::post('search_available_units', [BuildingManageController::class,'search_available_units']);
    Route::post('get_available_units_listing', [BuildingManageController::class,'get_available_units_listing']);
    Route::post('add_units', [BuildingManageController::class,'add_units']);
    Route::post('edit_available_units', [BuildingManageController::class,'edit_available_units']);
    Route::post('get_all_images_by_available_unit_id', [BuildingManageController::class,'get_all_images_by_available_unit_id']);
    Route::post('delete_image_of_available_unit', [BuildingManageController::class,'delete_image_of_available_unit']);
    Route::post('add_new_image_to_available_unit', [BuildingManageController::class,'add_new_image_to_available_unit']);
    Route::get('view_available_unit/{available_unit_id}', [BuildingManageController::class,'view_available_unit']);
    Route::post('activate_deactivate_units', [BuildingManageController::class,'activate_deactivate_units']);

    // tenant_units
    Route::post('search_all_units', [BuildingManageController::class,'search_all_units']);
    Route::post('get_tenant_units_list', [BuildingManageController::class,'get_tenant_units_list']);
    //owners_dropdown of PM
    Route::get('owners_dropdown_by_pm', [BuildingManageController::class,'owners_dropdown_by_pm']);
    Route::get('tenants_dropdown_by_pm_company_id', [BuildingManageController::class,'tenants_dropdown_by_pm_company_id']);
    Route::post('add_tenant_units', [BuildingManageController::class,'add_tenant_units']);
    Route::get('get_tenant_units_info/{unit_id}', [BuildingManageController::class,'get_tenant_units_info']);
    Route::post('delete_tenant_units', [BuildingManageController::class,'delete_tenant_units']);
    Route::post('edit_tenant_units', [BuildingManageController::class,'edit_tenant_units']);
    Route::post('activate_deactivate_tenant_units', [BuildingManageController::class,'activate_deactivate_tenant_units']);

    #contracts apis
    Route::get('building_dropdown_by_company_id', [BuildingManageController::class,'building_dropdown_by_company_id']);
    Route::post('tenant_unit_dropdown_by_building_id', [BuildingManageController::class,'tenant_unit_dropdown_by_building_id']);
    Route::post('add_contracts', [BuildingManageController::class,'add_contracts']);
    Route::post('update_contract', [PropertyManagerController::class,'update_contract']);
    Route::post('all_files_by_contract_id', [PropertyManagerController::class,'all_files_by_contract_id']);
    Route::post('delete_contract_file_by_file_id', [PropertyManagerController::class,'delete_contract_file_by_file_id']);
    Route::post('add_new_contract_file', [PropertyManagerController::class,'add_new_contract_file']);

    Route::get('get_tenant_by_unit_id/{unit_id}', [BuildingManageController::class,'get_tenant_by_unit_id']);
    Route::post('search_contract_list', [BuildingManageTwoController::class,'search_contract_list']);

   // tenant unit- show popup on change tenant
    Route::post('on_change_tenant_drop_down_of_tenant_unit_edit', [BuildingManageTwoController::class,'on_change_tenant_drop_down_of_tenant_unit_edit']);
    // tenant unit- take input from popup
    Route::post('accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit', [BuildingManageTwoController::class,'accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit']);

    //case 4.1
    // for edit and view tenant_unit
    Route::post('disconnect_popup_for_tenant_unit', [BuildingManageTwoController::class,'disconnect_popup_for_tenant_unit']);
    Route::post('disconnect_confirm_decline_for_tenant_unit', [BuildingManageTwoController::class,'disconnect_confirm_decline_for_tenant_unit']);

    //case 4.2
    // for view tenant
    // Route::post('unlinked_for_tenant_view', [BuildingManageTwoController::class,'unlinked_for_tenant_view']);
    // Route::post('unlinked_confirm_decline_for_tenant_view', [BuildingManageTwoController::class,'unlinked_confirm_decline_for_tenant_view']);

    //maintenance
    Route::post('maintanence_request_list_by_company_id',
    [\App\Http\Controllers\MaintanenceTwoController::class,'maintanence_request_list_by_company_id']);

    Route::post('unread_comment_maintenance_req_list',
    [\App\Http\Controllers\MaintanenceTwoController::class,'unread_comment_maintenance_req_list']);//tenant chat to pm

    Route::post('expenses_list_by_company_id',
    [\App\Http\Controllers\MaintanenceTwoController::class,'expenses_list_by_company_id']);


    Route::post('view_expenses_by_id',
    [\App\Http\Controllers\MaintanenceTwoController::class,'view_expenses_by_id']);

    //PM notifications
    Route::get('tenant_drop_down_at_notification', [\App\Http\Controllers\NotificationController::class,'tenant_drop_down_at_notification']);
    Route::post('pm_send_notification_to_tenant', [\App\Http\Controllers\NotificationController::class,'pm_send_notification_to_tenant']);
    Route::post('view_tenant_notification_for_pm', [\App\Http\Controllers\NotificationController::class,'view_tenant_notification_for_pm']);

    Route::group([ 'middleware' => 'throttle:per_fifteen_minutes'], function () {
        Route::get('admin_to_pm_notification_count', [\App\Http\Controllers\NotificationController::class,'admin_to_pm_notification_count']);
    });

    //same api for mobile and pm
    Route::post('update_pm_unread_count_for_pm', [\App\Http\Controllers\TenantController::class,'update_pm_unread_count']);


});

// Route::get('v1/testing_test', [PropertyManagerController::class,'testing_test']);


include('api_pm.php');
include('api_admin.php');
include('api_mobile.php');


