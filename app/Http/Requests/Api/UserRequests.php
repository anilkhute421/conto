<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserRequests extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    public static function edit_owner_request(){
        return [
            'owner_id'=> 'required|numeric|exists:owners,id',
            'name'  => 'required|max:120',
            'phone'  =>    'required|digits_between:8,12',
            // 'remarks' => 'required',
            'email' =>   'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
        ];
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function login_rules()
    {
        return [
             'email'      => 'required|',
             'password' =>    'required',
        ];
    }


    public static function add_owner_request(){
        return [
            'name'      => 'required|max:120',
            'phone' =>     'required|digits_between:8,12',
            'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
       ];
    }


    public static function add_units_request(){
        return [
            'unit_num'      => 'required',
            'building_id' =>     'required|numeric|exists:buildings,id',
            'address' =>    'nullable',
            'units_file' => 'required|max:200000',
            'rooms' =>    'required',
            'bathrooms' =>    'required',
            'area_sqm' => 'nullable|numeric',
            'monthly_rent' => 'required|digits_between:1,8'
       ];
    }

    public static function get_currency_code_by_building_id(){
        return [
            'building_id' =>     'required|numeric|exists:buildings,id',
       ];
    }


    public static function pm_profile_update()
    {
        return [
             'name'      => 'required|max:120',
             'email' =>    'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:property_managers',
             'phone' =>    'required|min:11|numeric',
             'role_id' =>    'required|numeric',
             'country_id' =>    'required|numeric',
             'pm_company_id' =>    'required|numeric',
             'office_contact_no' =>    'required|numeric',
        ];
    }

    public static function pm_profile_update_rule()
    {
        return [
             'name'      => 'required|max:120',
             'email' =>    'required',
             'phone' =>    'required|min:11|numeric',
             'role_id' =>    'required|numeric',
             'pm_company_id' =>    'required|numeric',
             'office_contact_no' =>    'required|numeric',
        ];
    }


    public static function change_password_request(){
        return [
            'current_password' => 'required' ,
            'new_password'  => 'required' ,
            'confirm_new_password' => 'min:7|required_with:new_password|same:new_password'
        ];
    }

    public static function add_building_request(){
        return [
            'building_name' => 'required' ,
            'address'  => 'required' ,
            'location'  => 'required' ,
            'description'  => 'required' ,
        ];
    }

    public static function edit_available_units(){
        return [
            'unit_id' => 'required|exists:avaliable_units,id' ,
            'unit_num' => 'required' ,
            'building_id'  => 'required|exists:buildings,id' ,
            'address'  => 'nullable' ,
            'rooms'  => 'required' ,
            'bathrooms'  => 'required' ,
            'area'  => 'nullable|required' ,
            'monthly_rent'  => 'required|digits_between:1,8',
        ];
    }

    public static function edit_tenant_units(){
        return [
                'unit_id' => 'required|exists:tenants_units,id' ,
                'unit_num' => 'required' ,
                'building_id' => 'required|numeric|exists:buildings,id' ,
                'owner_id' => 'required|numeric' ,
                'tenant_id' => 'required|numeric' ,
                'rooms'  =>  'required' ,
                'bathrooms' =>  'required' ,
                'area'  => 'nullable|numeric' ,
                'monthly_rent' => 'required|digits_between:1,8' ,
                'maintenance_included'  => 'required|in:0,1', // 1 -> means show 0->hide (maintenance request)

        ];
    }

    public static function get_all_images_by_available_unit_id(){
        return [
            'available_unit_id' => 'required|exists:avaliable_units,id' ,
        ];
    }

    public static function delete_image_of_available_unit(){
        return [
            'available_unit_image_id' => 'required|exists:available_unit_image,id' ,
        ];
    }

    public static function add_new_image_to_available_unit(){
        return [
            'available_unit_id' => 'required|exists:avaliable_units,id' ,
            'available_unit_image' => 'required|mimes:jpeg,png,gif,jpg|max:10000',//10mb
            // 'available_unit_image' => 'required|max:10000',//10mb

        ];
    }


    //for tenant unit
    public static function add_tenant_units(){
        return [
            'unit_num' => 'required' ,
            'building_id'  => 'required|numeric|exists:buildings,id' ,
            'owner_id'  => 'required|numeric|exists:owners,id' ,
            'tenant_id'  => 'required|numeric|exists:tenants,id' ,
            'rooms'  => 'required' ,
            'bathrooms'  => 'required' ,
            'area'  => 'nullable|numeric' ,
            'monthly_rent'  => 'required|digits_between:1,8',
            'maintenance_included'  => 'required|in:0,1', // 1 -> means show 0->hide (maintenance request)
        ];
    }

     //for tenant unit
     public static function add_tenant_units_without_tenant(){
        return [
            'unit_num' => 'required' ,
            'building_id'  => 'required|numeric|exists:buildings,id' ,
            'owner_id'  => 'required|numeric|exists:owners,id' ,
            'tenant_id'  => 'required|numeric' ,
            'rooms'  => 'required' ,
            'bathrooms'  => 'required' ,
            'area'  => 'nullable|numeric',
            'monthly_rent'  => 'required|digits_between:1,8',
            'maintenance_included'  => 'required|in:0,1', // 1 -> means show 0->hide (maintenance request)

        ];
    }



    //for tenant unit
    public static function edit_units_request(){
        return [
            'unit_id' => 'required|numeric|exists:avaliable_units,id' ,
            'unit_num' => 'required' ,
            'building_id'  => 'required|numeric|exists:buildings,id' ,
            'owner_id'  => 'required|numeric|exists:owners,id' ,
            'tenant_id'  => 'required|numeric|exists:tenants,id' ,
            'rooms'  => 'required' ,
            'bathrooms'  => 'required' ,
            'area'  => 'required' ,
            'monthly_rent'  => 'required|digits_between:1,8',
            'description'  => 'required' ,
        ];
    }

    public static function update_building_request(){
        return [
            'building_id' => 'required|numeric|exists:buildings,id' ,
            'building_name' => 'required' ,
            'address'  => 'required' ,
            'location'  => 'required' ,
            'description'  => 'required' ,
        ];
    }

    public static function add_contract_request(){
        return [
            'contract_name' => 'required' ,
            'contract_media' => 'required' ,
            'building_id'  => 'required|numeric|exists:buildings,id' ,
            'unit_id'  =>   'required|numeric|exists:tenants_units,id' ,
            'start_date'  => 'required|date' ,
            'expiry_date'  => 'required|date' ,
            'tenant_id'  => 'required|numeric',
        ];
    }

    public static function update_contract_request(){
        return [
            'contract_id' => 'required|numeric|exists:contracts_tables,id' ,
            'contract_name' => 'required' ,
            'building_id'  => 'required|numeric|exists:buildings,id' ,
            'unit_id'  =>   'required|numeric|exists:tenants_units,id' ,
            'start_date'  => 'required|date' ,
            'expiry_date'  => 'required|date' ,
            'tenant_id'  => 'required|numeric',
        ];
    }


    public static function add_new_contract_file(){
        return [
            'contract_id' => 'required|exists:contracts_tables,id',
            // 'contract_file' => 'required|mimes:jpeg,png,gif,jpg,pdf',
            'contract_file' => 'required',

        ];
    }




}
