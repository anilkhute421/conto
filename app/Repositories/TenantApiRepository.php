<?php

namespace App\Repositories;

use App\Models\TenantModel;
use App\Http\Controllers\Api\ApiBaseController;
use App\Models\CountryCurrencyModel;
use App\Models\RolesModel;
use App\Models\TenantTeampModel;
use App\Models\BuildingModel;
use App\Models\Country;
use App\Models\PropertyManager;
use App\Models\PropertyManagerCompany;
use App\Helpers\TenaHelper;
use Hash;

class TenantApiRepository extends ApiBaseController
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'first_name'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(){
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(){
        return TenantModel::class;
    }

    /**
     * validateDomain
     *
     * @param  mixed $email
     * @return void
     */


    /**
     * signupManually
     *
     * @param  mixed $inputs
     * @return void
     */
    public static function signup_manually($inputs,$request){

        # Hashing password

        $inputs['password'] = \Hash::make($inputs['password']);

        $inputs['address'] = '';
        // remove before project deliver.
        $inputs['language'] = '';
        
        $inputs['status'] = 0;
        $inputs['otp'] = 0;
        $inputs['is_email_verify'] = 1;
        $inputs['is_phone_verify'] = 1;
        // assign tenant_code
        $inputs['tenant_code'] = '';
        #get pm deatils through the building id.
        // $inputs['property_manager_id'] = 0;
        $inputs['property_manager_id'] = BuildingModel::join('property_managers', 'buildings.property_manager_id', '=', 'property_managers.id')->value('property_manager_id');
       # Creating tenant and return tenant details.
        $tenant = TenantModel::create($inputs);
        $user = TenantModel::find($tenant->id);
        $result = $user;
        // $result['token'] = $user->createToken('app' , ['tenant'])->plainTextToken;
        $result['token'] = '';

        return $result;
    }

    public static function tenant_login($user){
        $user->tokens()->delete();
        $result = $user;
        $result['country_name'] = Country::where('id' , $user->country_id )->value('country');
        $result['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
        RolesModel::where('id' , $user->role_id )->value('role_title'):'';
        $result['token'] = $user->createToken('app' , ['tenant'])->plainTextToken;
    //    dd($user->property_manager_id);

        $result['company_name'] = PropertyManagerCompany::where('id' , $user->property_manager_id)->value('name');
        return $result;
    }


   }
