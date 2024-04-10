<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('v1/mobile_app_versions_check', [\App\Http\Controllers\MobileApiController::class,'mobile_app_versions_check']);

Route::get('v1/get_all_countries', [TenantController::class,'get_all_countries']);

Route::post('v1/fetch_property_managers_by_country_id', [TenantController::class,'fetch_property_managers_by_country_id']);

Route::post('v1/fetch_building_by_property_managers_id', [TenantController::class,'fetch_building_by_property_managers_id']);

Route::post('v1/fetch_unit_no_by_building_id', [TenantController::class,'fetch_unit_no_by_building_id']);

Route::post('v1/signup_manually', [TenantController::class,'signup_manually']);

Route::post('v1/tenant_login', [TenantController::class,'tenant_login']);

Route::get('v1/tenant_terms_condition_details', [TenantController::class,'tenant_terms_condition_details']);

Route::get('v1/tenant_privacy_policy_details', [TenantController::class,'tenant_privacy_policy_details']);

// Route::post('v1/tenant_verify_otp', [TenantController::class,'tenant_verify_otp']);

Route::post('v1/tenant_forgot_password', [TenantController::class,'tenant_forgot_password']);

// Route::post('v1/resend_otp', [TenantController::class,'resend_otp']);

Route::post('v1/look_up_tenant_email', [TenantController::class,'look_up_tenant_email']);


Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:tenant' ]], function(){

    Route::get('tenant_get_details', [TenantController::class,'tenant_get_details']);

    Route::post('tenant_update_details', [TenantController::class,'tenant_update_details']);

    Route::post('tenant_reset_password', [TenantController::class,'tenant_reset_password']);

    Route::get('get_pm_deatils', [TenantController::class,'get_pm_deatils']);

    Route::post('pm_feedback', [TenantController::class,'pm_feedback']);

    Route::post('tenant_available_units', [TenantController::class,'tenant_available_units']);

    Route::post('tenant_available_unit_details', [TenantController::class,'tenant_available_unit_details']);

    Route::get('tenant_units', [TenantController::class,'tenant_units']);

    Route::post('tenant_unit_details', [TenantController::class,'tenant_unit_details']);

    Route::post('tenant_contract_details', [TenantController::class,'tenant_contract_details']);

    Route::post('upcoming_payment_by_tenant_id', [Api\ATenantTwoController::class,'upcoming_payment_by_tenant_id']);

    Route::post('overdue_payment_by_tenant_id', [Api\ATenantTwoController::class,'overdue_payment_by_tenant_id']);

    Route::post('history_payment_by_tenant_id', [Api\ATenantTwoController::class,'history_payment_by_tenant_id']);

    Route::post('maintance_request_for', [Api\ATenantTwoController::class,'maintance_request_for']);

    Route::post('add_maintenance_request_for_tenant', [Api\ATenantTwoController::class,'add_maintenance_request_for_tenant']);

    Route::post('current_request_list_by_tenant_id', [Api\ATenantTwoController::class,'current_request_list_by_tenant_id']);

    Route::post('close_request_list_by_tenant_id', [Api\ATenantTwoController::class,'close_request_list_by_tenant_id']);

    Route::post('cancel_request_list_by_tenant_id', [Api\ATenantTwoController::class,'cancel_request_list_by_tenant_id']);

    Route::post('cancel_request_by_maintenance_request_id', [Api\ATenantTwoController::class,'cancel_request_by_maintenance_request_id']);

    Route::post('view_maintanence_request_by_maintenance_request_id', [Api\ATenantTwoController::class,'view_maintanence_request_by_maintenance_request_id']);

    Route::post('upload_maintenance_files_by_maintenance_request_id', [Api\ATenantTwoController::class,'upload_maintenance_files_by_maintenance_request_id']);

    Route::post('fetch_maintanence_request_all_attachment_by_maintenance_request_id', [Api\ATenantTwoController::class,'fetch_maintanence_request_all_attachment_by_maintenance_request_id']);

    Route::post('delete_attachment_by_maintenance_request_id', [Api\ATenantThreeController::class,'delete_attachment_by_maintenance_request_id']);

    Route::post('upload_comment_media_by_maintenance_request_id', [Api\ATenantThreeController::class,'upload_comment_media_by_maintenance_request_id']);

    Route::post('tenant_notification_list', [Api\ATenantThreeController::class,'tenant_notification_list']);

    Route::post('view_tenant_notification_list', [Api\ATenantThreeController::class,'view_tenant_notification_list']);

    Route::post('view_tenant_contract_details', [TenantController::class,'view_tenant_contract_details']);

    Route::group([ 'middleware' => 'throttle:per_fifteen_minutes'], function () {
        Route::post('notification_count_for_tenant', [TenantController::class,'notification_count_for_tenant']);
    });

    Route::post('update_pm_unread_count', [TenantController::class,'update_pm_unread_count']);


})//auth



?>
