<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;



Route::get('/getDownload', [HomeController::class,'getDownload']);//?
Route::get('/', [HomeController::class,'index']);

//excel export urls
Route::get('/excel_export/{pm_id}/{export_type}' , [HomeController::class,'excel_export']);
Route::get('/excel_export_contract_expiring2m' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_contract_expiring2m']);
Route::get('/excel_export_card_closed_request' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_closed_request']);
Route::get('/excel_export_card_open_request' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_open_request']);
Route::get('/excel_export_card_settled_payment' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_set_payment']);
Route::get('/excel_export_card_overdue_payment' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_overdue_payment']);
Route::get('/excel_export_card_upcoming_payment' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_upcoming_payment']);
Route::get('/excel_export_card_maintenance_expense' , [\App\Http\Controllers\Api\ADashboardController::class,'excel_export_card_maintenance_expense']);



Route::get('login', function() {
    return response()->json(['message' => 'Unauthorized.', 'status' => 401 ],  200);
})->name('login');

Route::get('/change_password/{unique_email_key}/{user_id}', [HomeController::class,'change_password']);//?

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

// Route::get('tenant_privacy_policy', [PrivacyTermControoler::class, 'tenant_privacy_policy']);
// Route::get('tenant_term_condition', [PrivacyTermControoler::class, 'tenant_term_condition']);
// Route::get('admin_forgot_password/{key}', [PrivacyTermControoler::class, 'admin_forgot_password']);
// Route::post('admin_change_password', [AdminController::class, 'admin_change_password'])->name('admin_change_password');


Route::get('forgot_password/{key}', [PrivacyTermControoler::class, 'forgot_password']);// tenant
// tenant
Route::post('post_change_password', [PrivacyTermControoler::class, 'post_change_password'])->name('post_change_password');


// term & condition for property manager
Route::get('terms_condition/en/property_manager', [AdminTwoController::class,'pm_en_term_condition']);
Route::get('terms_condition/ar/property_manager', [AdminTwoController::class,'pm_ar_term_condition']);

// term & condition for tenant
Route::get('terms_condition/en/tenant', [AdminTwoController::class,'tenant_en_term_condition']);
Route::get('terms_condition/ar/tenant', [AdminTwoController::class,'tenant_ar_term_condition']);

// privacy policy for property manager
Route::get('privacy_policy/en/property_manager', [AdminTwoController::class,'pm_en_privacy_policy']);
Route::get('privacy_policy/ar/property_manager', [AdminTwoController::class,'pm_ar_privacy_policy']);

// privacy policy for tenant
Route::get('privacy_policy/en/tenant', [AdminTwoController::class,'tenant_en_privacy_policy']);
Route::get('privacy_policy/ar/tenant', [AdminTwoController::class,'tenant_ar_privacy_policy']);


// Route::get('blade', [AdminTwoController::class,'blade']);
