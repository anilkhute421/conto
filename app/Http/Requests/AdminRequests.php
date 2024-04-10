<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequests extends FormRequest
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
    public static function login_rules()
    {
        return [
            'email'      => 'required|email',
            'password' =>    'required',
        ];
    }

    public static function change_password_request(){
        return [
            'current_password' => 'required' ,
            'new_password'  => 'required' ,
            'confirm_new_password' => 'min:7|required_with:new_password|same:new_password'
        ];
    }

    public static function update_contact_info()
    {
        return [
            //  'name'      => 'optional|max:20',
             'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
             'phone' =>    'required|digits_between:8,12',
            //  'username' =>    'optional',
        ];
    }

    public static function add_property_manager()
    {
        return [
             'name'      => 'required|max:20',
             'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:property_managers,email',
             'phone' =>    'required|digits_between:8,12',
             'office_contact_no' =>    'required|digits_between:8,12',
             'company' =>    'required',//dropdown
             'role' =>    'required',//dropdown
             'password' =>    'required|min:7|max:20',

        ];
    }

    public static function add_pm_notification()
    {
        return [
             'title'      => 'required|min:3|max:50',
             'message' =>    'required|min:5|max:500',
             'message_language' =>    'required|in:en,ar',
        ];
    }

    public static function add_tenant_notification()
    {
        return [
             'title'      => 'required|min:3|max:50',
             'message' =>    'required|min:5|max:500',
             'message_language' =>    'required|in:en,ar',
        ];
    }

    public static function edit_property_manager($userId)
    {
        return [
             'name'      => 'required|max:20',
             'email' =>    'required|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:property_managers,email,'. $userId,
             'phone' =>    'required|digits_between:8,12',
             'office_contact_no' =>    'required|digits_between:8,12',
             'company' =>    'required',//dropdown
             'role' =>    'required',//dropdown

        ];
    }


    public static function edit_tenant($userId)
    {
        return [
             'first_name'     => 'required|max:20',
             'email' =>    'required|max:200|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:tenants,email,'. $userId,
             'phone' =>    'required|digits_between:8,12|unique:tenants,phone,'. $userId,

             'country' =>    'required',//dropdown
             'company' =>    'required',//dropdown
             'building_id' =>    'required',//dropdown
             'unit_id' =>    'required',//dropdown
        ];
    }


    public static function add_property_manager_company()
    {
        return [
             'name'      => 'required|max:20|unique:property_manager_companies,name',
             'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
             'phone' =>    'required|digits_between:8,12',
             'office_contact_no' =>    'required|digits_between:8,12',
             'location' =>    'required|max:500',
             'country' =>    'required',
             'currency' =>    'required',
        ];
    }

    public static function edit_property_manager_company($Id)
    {
        return [
             'name'      => 'required|max:20|unique:property_manager_companies,name,'. $Id,
             'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
             'phone' =>    'required|digits_between:8,12',
             'office_contact_no' =>    'required|digits_between:8,12',
             'location' =>    'required|max:500',
             'country' =>    'required',
             'currency' =>    'required',
        ];
    }

    public static function edit_manage_cms($Id)
    {
        return [
            //  'title'      => 'required|max:20'. $Id,
            //  'page_for' =>    'required',
            //  'page_language' =>    'required',
             'description' =>    'required',

        ];
    }

}
