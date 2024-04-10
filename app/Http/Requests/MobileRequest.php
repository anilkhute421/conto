<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileRequest extends FormRequest
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

    public static function fetch_property_managers_by_country_id_rule(){
        return [
            'country_id' => 'required|numeric|exists:countries,id'
            // 'maintenance_request_for_id' => 'required|numeric|exists:maitinance_request_for,id',

        ];
    }

    // note:- here property_manager_id is pm_company_id.
    public static function fetch_building_by_property_managers_id(){
        return [
            'property_manager_id' => 'required|numeric|exists:property_manager_companies,id'
        ];
    }

    public static function fetch_unit_no_by_building_id(){
        return [
            'building_id' => 'required|numeric|exists:buildings,id'
        ];
    }

    public static function signup_manually_tenant(){
        return [
            'first_name'    => 'required|max:20',
            'last_name'     => 'required|max:20',
            'email'         => 'required|email',
            'password'      => 'required|min:7|max:20',
            'phone'         => 'required|digits_between:8,12',
            // 'country_id'    => 'required|numeric',
            'country_id' => 'required|numeric|exists:countries,id',

            'pm_company_id' => 'required|numeric|exists:property_manager_companies,id',
            // 'building_id'   => 'required|numeric',
            'building_id' => 'required|numeric|exists:buildings,id',
            // 'unit_id'       => 'required|numeric',
            'unit_id'       => 'required|numeric|exists:tenants_units,id',
           
            'country_code'  => 'required|numeric', //todo exits in countries table.
             'email_otp'       => 'required|numeric',
        //    
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
            'new_password'  => 'required|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/' ,
            'confirm_new_password' => 'min:6|required_with:new_password|same:new_password'
        ];
    }

    public static function forget_password_rules (){
        return [
            'password'  => 'required|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirmPassword' => 'required|same:password'
        ];
    }
    

    public static function tenant_update_details_rules (){
        return [
            'first_name'    => 'required|max:20',
            'last_name'     => 'required|max:20',
            'phone'         => 'required|digits_between:8,12',
            'email'         =>  'required|email'
        ];
    
    }

    public static function pm_profile_update (){
        return [
            'first_name'    => 'required|max:20',
            'last_name'     => 'required|max:20',
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


    public static function add_maintenance_request_for_tenant(){
        return [
            'maintenance_request_for_id' => 'required|numeric|exists:maitinance_request_for,id',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
            'description'  => 'required|max:500',
            'preferred_date_time'  => 'required',
        ];
    }

    public static function upload_maintenance_files_by_maintenance_request_id(){
        return [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
            'file_type' => 'required|in:1,2', // 1-image, 2-video
            'attachment' => 'required',
            'thumbnail_name' => 'required',
        ];
    }
    
    public static function upload_comment_media_by_maintenance_request_id(){
        return [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
            'media_type' => 'required|in:1,2', // 1-image, 2-video
            'attachment' => 'required',
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
