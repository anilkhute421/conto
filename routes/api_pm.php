<?php

namespace App\Http\Controllers\Api  ;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:property_manager' , 'headerlang']], function(){

    // contract
    Route::post('contract_list_by_company_id', [PropertyManagerThreeController::class,'contract_list_by_company_id']);
    Route::post('view_contract_by_contract_id', [PropertyManagerThreeController::class,'view_contract_by_contract_id']);
    Route::post('delete_contract', [PropertyManagerThreeController::class,'delete_contract']);

    // payment
    Route::post('search_payment_list', [PropertyManagerThreeController::class,'search_payment_list']);
    Route::post('payment_list_by_company_id', [PropertyManagerThreeController::class,'payment_list_by_company_id']);
    Route::post('add_payment', [PropertyManagerThreeController::class,'add_payment']);
    Route::post('update_payment', [PropertyManagerThreeController::class,'update_payment']);
    Route::post('delete_payment', [PropertyManagerThreeController::class,'delete_payment']);
    Route::post('view_payment_by_payment_id', [PropertyManagerThreeController::class,'view_payment_by_payment_id']);

    // tenants
    Route::post('search_all_tenants', [TenantOneController::class,'search_all_tenants']);
    Route::post('all_tenant_list_by_company_id', [TenantOneController::class,'all_tenant_list_by_company_id']);
    Route::post('tenant_request_list_by_company_id', [TenantOneController::class,'tenant_request_list_by_company_id']);
    Route::post('view_tenant_request_by_tenant_id', [TenantOneController::class,'view_tenant_request_by_tenant_id']);

    Route::post('unlinked_tenant_list_by_company_id', [TenantOneController::class,'unlinked_tenant_list_by_company_id']);
    Route::post('add_tenant', [TenantOneController::class,'add_tenant']);
    Route::post('view_tenant_by_tenant_id', [TenantOneController::class,'view_tenant_by_tenant_id']);
    Route::post('country_code_dropdown', [TenantOneController::class,'country_code_dropdown']);
    Route::post('delete_tenant_request', [TenantOneController::class,'delete_tenant_request']);
    Route::post('linked_tenant_list_by_company_id', [TenantOneController::class,'linked_tenant_list_by_company_id']);
    Route::post('update_tenant', [TenantOneController::class,'update_tenant']);

    // expert
    Route::post('search_expert', [ExpertController::class,'search_expert']);
    Route::post('experts_list_by_company_id', [ExpertController::class,'experts_list_by_company_id']);
    Route::get('specialties_dropdown', [ExpertController::class,'specialties_dropdown']);
    Route::post('add_experts', [ExpertController::class,'add_experts']);
    Route::post('update_experts', [ExpertController::class,'update_experts']);
    Route::post('delete_expert', [ExpertController::class,'delete_expert']);
    Route::post('view_expert_by_expert_id', [ExpertController::class,'view_expert_by_expert_id']);

    //expenes_dropdown
    Route::post('search_maintanence_expenses', [ExpenseController::class,'search_maintanence_expenses']);

    Route::get('expenes_dropdown', [ExpensesLinesController::class,'expenes_dropdown']);
    Route::post('request_dropdown', [ExpensesLinesController::class,'request_dropdown']);
    Route::post('add_expense', [ExpensesLinesController::class,'add_expense']);
    Route::post('delete_expense_item_image', [ExpensesLinesController::class,'delete_expense_item_image']);
    Route::post('delete_expense_item_by_id', [ExpensesLinesController::class,'delete_expense_item_by_id']);
    Route::post('add_image_to_old_item', [ExpensesLinesController::class,'add_image_to_old_item']);
    Route::post('update_expenses', [ExpensesLinesController::class,'update_expenses']);

    //maintance_request
    Route::post('search_maintanence_request', [MaintanceRequestController::class,'search_maintanence_request']);

    Route::get('maintance_request', [MaintanceRequestController::class,'maintance_request']);
    Route::get('expert_dropdown', [MaintanceRequestController::class,'expert_dropdown']);
    Route::post('add_maintanence_request', [MaintanceRequestController::class,'add_maintanence_request']);
    Route::post('view_maintanence_request_by_request_id', [MaintanceRequestController::class,'view_maintanence_request_by_request_id']);
    Route::post('unassign_expert_from_maintenance_request', [MaintanceRequestController::class,'unassign_expert_from_maintenance_request']);
    Route::post('update_maintanence_request_by_request_id', [MaintanceRequestController::class,'update_maintanence_request_by_request_id']);
    Route::post('upload_maintenance_files', [ExpenseController::class,'upload_maintenance_files']);
    Route::post('upload_comment_media', [ExpenseController::class,'upload_comment_media']);
    Route::post('fetch_maintanence_request_all_attachment', [ExpenseController::class,'fetch_maintanence_request_all_attachment']);
    Route::post('delete_attachment_by_id', [ExpenseController::class,'delete_attachment_by_id']);

    //contact request
    Route::post('pm_contact_to_admin', [BuildingThreeController::class,'pm_contact_to_admin']);


    //notification
    Route::post('tenant_notification_list_for_pm', [ExpensesLinesController::class,'tenant_notification_list_for_pm']);
    Route::post('admin_notification_list', [ExpensesLinesController::class,'admin_notification_list']);
    Route::post('view_admin_notification_for_pm', [ANotificationTwoController::class,'view_admin_notification_for_pm']);
    Route::post('pm_delete_tenant_noti', [ANotificationTwoController::class,'pm_delete_tenant_noti']);
    Route::post('search_tenant_noti_for_pm', [ANotificationTwoController::class,'search_tenant_noti_for_pm']);
    Route::post('search_admin_noti_for_pm', [ANotificationTwoController::class,'search_admin_noti_for_pm']);
    Route::post('subscribe_topic', [ANotificationTwoController::class,'subscribe_topic']);




    Route::post('payment_list_by_unit_id', [BuildingThreeController::class,'payment_list_by_unit_id']);

    Route::post('maintenance_request_list_by_unit_id', [BuildingThreeController::class,'maintenance_request_list_by_unit_id']);

    Route::post('contract_list_by_unit_id', [BuildingThreeController::class,'contract_list_by_unit_id']);

    Route::post('pm_logout', [BuildingThreeController::class,'pm_logout']);

    Route::post('dashboard_counts_or_dropdown', [ADashboardController::class,'dashboard_counts_or_dropdown']);

    Route::post('dashboard_filters', [ADashboardController::class,'dashboard_filters']);

    Route::post('tenant_verify_by_pm', [BuildingThreeController::class,'tenant_verify_by_pm']);

    Route::post('tenant_verify_by_pm', [BuildingThreeController::class,'tenant_verify_by_pm']);

    Route::get('tenant_details/{id}', [TestingController::class,'tenant_details']);

    Route::post('drill_down_list_by_card', [ADashboardController::class,'drill_down_list_by_card']);

    Route::post('drill_down_list_contract_expiring2m', [ADashboardController::class,'drill_down_list_contract_expiring2m']);

    Route::post('send_push_noti_for_frontend', [ANotificationTwoController::class,'send_push_noti_for_frontend']);


});//pm auth

//maintenance_request_detaills_for_expets
Route::post('maintenance_request_details_for_expets', [ExpenseController::class,'maintenance_request_details_for_expets']);

Route::get('test_final', [TestingController::class,'test_final']);

// Route::get('blade', [TestingController::class,'blade']);

Route::get('expireContracts', [CronController::class,'expireContracts']);

Route::get('expirePaymentStatusChange', [CronController::class,'expirePaymentStatusChange']);

?>
