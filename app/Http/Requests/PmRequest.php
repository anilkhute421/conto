<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }


    public static function add_payment(){
        return [
            'tenant_id'    => 'required|numeric',
            'building_id' => 'required|numeric|exists:buildings,id',
            'tenant_unit_id'       => 'required|numeric|exists:tenants_units,id',

            'remark'  => 'nullable|array',
            'remark.*'  => 'nullable',

            'payment_type' => 'required|array',
            'payment_type.*' => 'required|in:1,0',

            'payment_date'      => 'required|array',
            'payment_date.*'      => 'required|date',

            'amount'         => 'required|array',
            'amount.*'         => 'required|digits_between:1,8',

            // "expenses_lines_ids"    => "required|array|min:1|max:3",
            // "expenses_lines_ids.*"  => 'required|numeric|exists:expenseslines,id',

        ];
    }



    public static function add_cheque_payment(){
        return [
            'cheque_no'     => 'required|max:20',
            // 'status'    => 'required|in:1,2,3,4,5,6',

            'status'         => 'required|array',
            'status.*'         => 'required|in:1,2,3,4,5,6',
        ];
    }

    public static function add_manual_payment(){
        return [
            // 'status'    => 'required|in:1,2,3,4,6',

            'status'         => 'required|array',
            'status.*'         => 'required|in:1,2,3,4,6',

        ];
    }


    public static function update_payment(){
        return [
            'payment_id' => 'required|numeric|exists:payments,id' ,
            'payment_type' => 'required|in:1,0',
            'tenant_id'    => 'required|numeric',
            'payment_date'      => 'required|date',
            'amount'         => 'required|digits_between:1,8',
            'building_id' => 'required|numeric|exists:buildings,id',
            'tenant_unit_id'       => 'required|numeric|exists:tenants_units,id',
            'remark'  => 'nullable',
        ];
    }

    public static function edit_cheque_payment(){
        return [
            'cheque_no'     => 'required|max:20',
            'status'    => 'required|in:1,2,3,4,5,6',
        ];
    }

    public static function edit_manual_payment(){
        return [
            'status'    => 'required|in:1,2,3,4,6',
        ];
    }


    public static function add_tenant(){
        return [
            'first_name'    => 'required|max:20',
            'last_name'     => 'required|max:20',
            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
            'phone' => 'required|digits_between:8,12',
            'country_code'  => 'required|digits_between:1,3',
        ];
    }

    public static function add_tenant_email_unique(){
        return [

            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:tenants,email',

        ];
    }

    public static function update_tenant(){
        return [
            'tenant_id' =>   'required|numeric|exists:tenants,id',
            'first_name'    => 'required|max:20',
            'last_name'     => 'required|max:20',
            'phone' => 'required|digits_between:8,12',
            'country_code'  => 'required|digits_between:1,3',
        ];
    }

    public static function add_maintanence_request(){
        return [
            'tenant_id'    => 'required|numeric',
            'building_id' => 'required|numeric|exists:buildings,id',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
            'maintenance_request_id' => 'required|numeric|exists:maitinance_request_for,id',
            'status'  => 'required|in:1,2,3,4,5',
            'description'  => 'required|max:500',

        ];
    }

    public static function update_maintanence(){
        return [
            'request_id'  => 'required|numeric|exists:maintance_requests,id',
            'tenant_id'    => 'required|numeric',
            'building_id' => 'required|numeric|exists:buildings,id',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
            'maintenance_request_id' => 'required|numeric|exists:maitinance_request_for,id',
            'status'  => 'required|in:1,2,3,4,5',
            'description'  => 'required|max:500',
        ];
    }


    public static function add_experts(){
        return [
            'name'    => 'required|max:20',
            'email'    => 'required|email',
            'phone' => 'required|numeric',
            'speciality_id' => 'required',
            'remark'  => 'required',
            'country_code'  => 'required',
        ];
    }

    public static function update_experts(){
        return [
            'expert_id'  => 'required|numeric|exists:experts,id',
            'name'    => 'required|max:20',
            'email'    => 'required|email',
            'phone' => 'required|numeric',
            // 'speciality_id' => 'required|numeric',
            'remark'  => 'required',
            'country_code'  => 'required',
        ];
    }

    public static function add_expense(){
        return [
            'building_id' => 'required|numeric|exists:buildings,id',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
            'request_id' => 'required|numeric|exists:maintance_requests,id',
            'tenant_id'    => 'required|numeric',
        ];
    }

    public static function upload_maintenance_files(){
        return [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
            'file_type' => 'required|in:1,2', // 1-image, 2-video
            'attachment' => 'required',
            'thumbnail_name' => 'required',
        ];
    }


    public static function upload_comment_media(){
        return [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
            'media_type' => 'required|in:1,2', // 1-image, 2-video
            'attachment' => 'required',
        ];
    }






    public static function language_rule(){
        return [

            'language'  =>     'required|alpha',

        ];
    }

    public static function login_rule(){
        return [

            'email'      => 'required|exists:tenants,email',
            'password' =>    'required',

        ];
    }

    public static function reset_password_rules (){
        return [
            'current_password' => 'required' ,
            'new_password'  => 'required' ,
            'confirm_new_password' => 'min:6|required_with:new_password|same:new_password'
        ];
    }

    public static function forget_password_rules (){
        return [
            'password'  => 'required|same:confirmPassword|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirmPassword' => 'required'
        ];
    }


    public static function tenant_update_details_rules (){
        return [
            'first_name'    => 'required',
            'last_name'     => 'required',
            'phone'         => 'required|',
            'email'         =>  'required|'
        ];

    }

    public static function pm_profile_update (){
        return [
            'first_name'    => 'required',
            'last_name'     => 'required',
            // 'phone'         => 'required|numeric|min:10||unique:tenants,phone',
            'email'         =>  'required|email|unique:tenants,email'
        ];

    }


     public static function tenant_verify_otp(){
        return [
            'email_otp' => 'required|numeric',
        ];
    }

    public static function message(){
        return [
            'email_otp' => 'required|numeric',
        ];
    }


    public static function pm_feedback(){
        return [
            'feedback_message' => 'required',
        ];
    }

    public static function add_notification_by_pm(){
        return [
            'title' => 'required|max:50',
            'message' => 'required|max:500',

        ];

    }





        // public static function signup_manually_temp(){
    //     return [
    //         'first_name'    => 'required|alpha|min:3',
    //         'last_name'     => 'required|alpha|min:3',
    //         'email'         => 'required|email',
    //         'password'      => 'required|min:6',
    //         'phone'         => 'required|numeric|min:10',
    //         'country_id'    => 'required|numeric',
    //         'property_manager_id' => 'required|numeric',
    //         'building_id'   => 'required|numeric',
    //         'unit_id'       => 'required|numeric',
    //         'country_code'  => 'required|numeric',
    //        // 'os_type'       => 'required|numeric',
    //     //
    //     ];
    // }


}
