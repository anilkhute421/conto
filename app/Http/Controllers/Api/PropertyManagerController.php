<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiBaseController;
use Illuminate\Foundation\Auth\AuthenticatePropertyManager;
use App\Http\Requests\Api\UserRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\PropertyManager;
use App\Models\CountryCurrencyModel;
use App\Models\RolesModel;
use App\Models\ContractModel;
use App\Models\PropertyManagerCompany;
use Hash;
use Mail;
use App\Helpers\Helper;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class PropertyManagerController extends ApiBaseController
{
    // public function login (Request $request){
    //    // dd('die');
    //     // $all_headers = apache_request_headers();
    //     // $all_headers['url'] = \Request::url();
    //     // $all_headers =   array_merge($all_headers , $request->all() );
    //     // \Log::info( $all_headers);
    //     $validator = validator($request->all(), UserRequests::login_rules());
    //     if($validator->fails()){
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201 ,200);
    //     }
    //     $user = PropertyManager::where('email', $request->email)->first();
    //     if (! $user || ! Hash::check($request->password, $user->password)) {
    //         return $this->sendSingleFieldError('Invalid credentials entered',201,200);
    //     }
    //
    //     $user->tokens()->delete();
    //     $result = $user;
    //     $result['country_name'] = CountryCurrencyModel::where('id' , $user->country_id )->first()->value('country');
    //     $result['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
    //     RolesModel::where('id' , $user->role_id )->first()->value('role_title'):'';
    //     $result['token'] = $user->createToken('pm' , ['property_manager'])->plainTextToken;
    //     $result['company_name'] = PropertyManagerCompany::where('id' , $user->pm_company_id )->first()->value('name');
    //     return $this->sendResponse($result,'Login Successfull',200,200);
    // }



    public function view_pm_profile($id){
        $user = PropertyManager::where('id', $id)->first();
        $result = $user;

        $country_id = \DB::table('property_manager_companies')->where('id', $user->pm_company_id)->value('country_id');

        $result['country_name'] = \App\Models\Country::where('id' , $country_id )->value('country');
        $result['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
        RolesModel::where('id' , $user->role_id )->first()->value('role_title'):'';
        $result['company_name'] = PropertyManagerCompany::where('id' , $user->pm_company_id )->value('name');
        $result['role_details'] = \DB::table('roles')->where('id',$user->role_id)->first();

        return $this->sendResponse($result,'Pm profile details',200,200);
    }


    public function property_manager_profile(Request $request){
        return $this->sendResponse($request->user(),'',200,200);
    }

    public function update_pm_profile(Request $request){
       // echo trans('lang.msg'); die;
     $check= PropertyManager::where(['email' =>$request->email , 'id' => $request->user()->id ])->exists();
       if($check){
        $validator = validator($request->all(), UserRequests::pm_profile_update_rule());
            if($validator->fails()){
                return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
        }else{
            $validator = validator($request->all(), UserRequests::pm_profile_update());
            if($validator->fails()){
                return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
        }

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('profile','edit',$request->user()->id,$request->user()->id,$request->user()->pm_company_id,'', 'pm_profile_edit');

       PropertyManager::where('id', $request->user()->id )
       ->update(['name' => $request->name ,
        'email' => $request->email , 'phone' => $request->phone  ,  'role_id' => $request->role_id ,
        'pm_company_id' => $request->pm_company_id ,
        'office_contact_no' =>  $request->office_contact_no ]);
        $user = PropertyManager::where('id', $request->user()->id)->first();
        $user['country_name'] = \App\Models\Country::where('id' , $user->country_id )->value('country');
        $user['company_name'] = PropertyManagerCompany::where('id' , $user->pm_company_id )->value('name');
        $user['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
        RolesModel::where('id' , $user->role_id )->first()->value('role_title'):'';
        $user['role_details'] = \DB::table('roles')->where('id',$user->role_id)->first();
        // return $this->sendResponse($user , trans('lang.profile_update'), 200,200);
        return $this->sendResponse($user,__(app()->getLocale().'.profile_update'), 200,200);

    }


    public function change_password(Request $request){
        // $all_headers = apache_request_headers();
        // $all_headers['url'] = \Request::url();
        // $all_headers =   array_merge($all_headers , $request->all() );
        $validator = validator($request->all(), UserRequests::change_password_request());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $user = PropertyManager::where('id', $request->user()->id)->first();
         


        if (! $user || ! Hash::check($request->current_password, $user->password)) {
            // return $this->sendSingleFieldError('Invalid current password entered',201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.invalid_current_password'),201,200);

        }else{

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('profile','edit',$request->user()->id,$request->user()->id,$request->user()->pm_company_id,'pass update', 'pm_profile_change_pass');

             $user->update(['password' => Hash::make($request->confirm_new_password)]);
            //  return $this->sendResponse( [] , 'Password has been updated successfully' ,200,200);
             return $this->sendResponse( [] ,__(app()->getLocale().'.password_updated') ,200,200);

        }
    }

    public function get_roles(){
        $get_roles = RolesModel::select('role_title', 'id')->get();
        return $this->sendResponse( $get_roles , 'Get roles' ,200,200);
    }

    public function get_all_companies(){
        $get_all_companies = PropertyManagerCompany::select('name', 'id')->get();
         return $this->sendResponse( $get_all_companies , 'Get roles' ,200,200);
    }

    //post
    public function update_contract(Request $request){

        try {
            $validator = validator($request->all(),  UserRequests::update_contract_request());
            if($validator->fails()){
                return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
            // \Log::notice($request->all());
            if(Carbon::parse($request->expiry_date) < Carbon::parse($request->start_date)){
                // return $this->sendSingleFieldError('Expiry date must be greater than start date',201,200);
                return $this->sendSingleFieldError(__(app()->getLocale().'.expiry_date_greater_than_start_date'),201,200);

            }

            $expiry_date = ContractModel::where('id', $request->contract_id)->value('end_date');
            if(Carbon::parse($expiry_date) < Carbon::now()){
                // return $this->sendSingleFieldError('contract is expired, please create another contract record',201,200);
                return $this->sendSingleFieldError(__(app()->getLocale().'.contract_expired_create_another_contract'),201,200);

            }
            //If expiry date less than today the n expired
            //If expiry date greater the. Today then active
            $status = (Carbon::parse($request->expiry_date) < Carbon::now())?0:1;

            $old_contract_name =\App\models\Contract::where('id', $request->contract_id)->value('name');

            $contract = \App\models\Contract::where('id', $request->contract_id)
                ->update([
                    'name'=> $request->contract_name ,
                    'Tenant_id' => $request->tenant_id ,
                    'building_id' => $request->building_id ,
                    'unit_id' => $request->unit_id ,
                    'start_date' => Carbon::create($request->start_date) ,
                    'end_date' =>  Carbon::create($request->expiry_date),
                    'status' => $status// But we need active/expired auto status

            ]);

            //module,action,affected_record_id,pm_id,pm_company_id
           \App\Services\PmLogService::pm_log_entry('contract','edit',$request->contract_id,$request->user()->id,$request->user()->pm_company_id,$old_contract_name, 'contract_edit');

            // return $this->sendResponse([] ,'Contract has been updated successfully.',200,200);
            return $this->sendResponse([] ,__(app()->getLocale().'.contract_updated_successfully'),200,200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }

    //post
    public function all_files_by_contract_id(Request $request){

        $validator = validator($request->all(), [ 'contract_id' => 'required|numeric|exists:contracts_tables,id' ]);

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $ContractFiles = \App\models\ContractFilesModel::where('contract_id', $request->contract_id )
        ->select('id','file_name')->get();

        return $this->sendResponse($ContractFiles ,'all_files_by_contract_id',200,200);
    }

    //post
    public function delete_contract_file_by_file_id(Request $request){

        $validator = validator($request->all(), ['contract_file_id' => 'required|exists:contracts_files_tables,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $contract_id = \DB::table('contracts_files_tables')->where('id', $request->contract_file_id )->value('contract_id');

        $count = \DB::table('contracts_files_tables')->where('contract_id', $contract_id )->count();
        if($count == 1){
        //    return $this->sendSingleFieldError('Sorry, minimum 1 file should be exists!',201,200);
           return $this->sendSingleFieldError(__(app()->getLocale().'.minimum_one_file_exists'),201,200);

        }

        $file_name = \DB::table('contracts_files_tables')->where('id', $request->contract_file_id )->value('file_name');

        // delete file from storage
        \App\Services\FileUploadService::delete_contract_document_from_azure($file_name, 'delete_contract_file_by_file_id');

        \DB::table('contracts_files_tables')->where('id', $request->contract_file_id )->delete();

        // return $this->sendResponse([] ,'File deleted successfully',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.file_delete'),200,200);

    }


    //post
    public function add_new_contract_file(Request $request){

        $validator = validator($request->all(), UserRequests::add_new_contract_file());
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        if(!$request->hasFile('contract_file')){
            // return $this->sendSingleFieldError('contract_file is required',201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.file_required'),201,200);

        }

        $count = \DB::table('contracts_files_tables')->where('contract_id', $request->contract_id )->count();
        if($count >= 5){
        //    return $this->sendSingleFieldError('Sorry, maximum 5 files allowed!',201,200);
           return $this->sendSingleFieldError(__(app()->getLocale().'.max_five_files'),201,200);

        }

        $imageName = uniqid().'.'.$request->file('contract_file')->getClientOriginalExtension();

        // upload on storage
        \App\Services\FileUploadService::upload_contract_to_azure_doc($imageName,$request->file('contract_file'), 'add_new_contract_file');

        \App\models\ContractFilesModel::create(['contract_id' => $request->contract_id , 'file_name' => $imageName]);

        // return $this->sendResponse([] ,'New contract file added successfully.',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.new_file_added_successfully'),200,200);

    }



    // public function forgot_password_email(Request $request) {
    //     $validator = validator($request->all(), ['email' => 'required|email|exists:property_managers,email']);
    //     if($validator->fails()){
    //         return $this->sendSingleFieldError($validator->errors()->first(),201,200);
    //     }
    //     $user = PropertyManager::where('email', $request->email )->first();
    //     $password = Helper::randomPassword();
    //     $user->update(['password' => Hash::make($password)]);
    //     $emailData =  array(
    //         'password'   => $password
    //     );
    //     Mail::to($request->email)->send(new \App\Mail\SendPassword($emailData));
    //     return $this->sendResponse( [] , 'Generated password has been sent to your email.' ,200,200);
    //  }

    public function testing_test(){
        dd(Carbon::now());
        return $this->sendResponse([] , 'test' ,200,200);
    }


}
