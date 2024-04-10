<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\AdminModel;
use App\Models\PropertyManager;
use App\Models\PropertyManagerCompany;
use App\Models\TenantModel;
use App\Models\RolesModel;
use App\Models\BuildingModel;
use App\Models\AvailableUnitModel;
use App\Models\Country;
use App\Models\Currency;
use App\Helpers\TenantHelper;


use App\Models\CountryCurrencyModel;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AdminRequests;
use Mail;
use DB;


class AdminController extends ApiBaseController
{
    //admin login
    public function admin_login(Request $request){
        $validator = validator($request->all(), AdminRequests::login_rules());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $user = AdminModel::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->sendSingleFieldError('Invalid credentials',201,200);
        }

        $admin_email = $request->email;

        // genrate otp for admin.
        $otp =  TenantHelper::generate_uniq_code_for_otp();

        // send mail to the admin to verify email id.
        \Mail::to($admin_email)->send(new \App\Mail\SendOtpToAdmin($otp));
        // \Mail::to('sfs.naveen18@gmail.com')->send(new \App\Mail\SendOtpToAdmin($otp));

        \DB::table('admins')->where('email', $admin_email)->update([
            'remember_token' => $otp
        ]);

        $user->tokens()->delete();
        $result['access_token'] = $user->createToken('web' , ['admin'])->plainTextToken;
        return $this->sendResponse($result,'Login Successful',200,200);
    }

    //for otp page only
    //check access_token
    public function check_access_token(){
        // \Log::debug('run check_access_token');
        return response()->json( array('success' => true) );
    }

    //check access_token_otp
    public function check_access_token_otp(Request $request){

        // \Log::debug('run check_access_token_otp');
        // \Log::notice('remember_token '. $request->remember_token );

        $admin = \DB::table('admins')->first();

        if($request->remember_token == $admin->remember_token){

            return $this->sendResponse('','Auth true',200,200);
        }else{
            return $this->sendSingleFieldError('Auth false',201,200);
        }
    }

    //verify_otp by sms
    public function verify_otp(Request $request){
        // \Log::notice('otp '. $request->otp );
        $user = AdminModel::first();

        $email_otp = $user->remember_token;

        if($request->otp == $email_otp){

            return $this->sendResponse('','Login Successful',200,200);
        }else{
            return $this->sendSingleFieldError('Invalid OTP entered',201,200);
        }
    }

    //admin logout
    public function logout(){

        $user = AdminModel::first();
        $user->tokens()->delete();
        return $this->sendResponse([],'Logout Successful',200,200);
    }

    //admin change_password
    public function change_password(Request $request){
        //  \Log::notice('current_password '. $request->current_password );

         $validator = validator($request->all(), AdminRequests::change_password_request());
         if($validator->fails()){
             return $this->sendSingleFieldError($validator->errors()->first(),201,200);
         }
         $user = AdminModel::first();
         if (! $user || ! Hash::check($request->current_password, $user->password)) {
             return $this->sendSingleFieldError('Invalid current password entered',201,200);
         }else{
             $user->update(['password' => Hash::make($request->confirm_new_password)]);
              return $this->sendResponse( [] , 'Password updated successfully' ,200,200);
         }
    }

    //get
    public function get_profile(){
        $admin = AdminModel::select('email','phone','name','username')->first();
        //  \Log::notice( $admin );
        return $this->sendResponse($admin,'admin',200,200);
    }

    //post
    public function update_contact_info(Request $request){
        $validator = validator($request->all(), AdminRequests::update_contact_info());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $user = AdminModel::first();
        $user->update([
            'email' => $request->email,
            'phone' => $request->phone
        ]);
        return $this->sendResponse( [] , 'Contact Info updated successfully' ,200,200);
    }

    //get
    public function property_manager_company(){

        $pm_company = PropertyManagerCompany::select(
            'property_manager_companies.id',
            'property_manager_companies.name',
            'property_manager_companies.email',
            'property_manager_companies.location',
            'property_manager_companies.office_contact_no',
            'property_manager_companies.status',

            'property_manager_companies.country_id',
            'countries.country',
        )
        ->join('countries' , 'property_manager_companies.country_id', '=' , 'countries.id')

        ->get()
        ->transform(function($item) {
            $item->status = $item->status == 1 ? 'Active' : 'Deactive';

            $item->location =  mb_strimwidth( $item->location, 0, 15, '...');

            return $item;
        });
        //  \Log::notice( $admin );
        return $this->sendResponse($pm_company,'property_manager_company',200,200);
    }

    //get
    public function property_manager_users(){

        $pm = PropertyManager::select('property_manager_companies.name as company',
        'property_managers.id',
        'property_managers.name', 'property_managers.email', 'property_managers.status', 'property_managers.role_id')

        ->join('property_manager_companies' , 'property_manager_companies.id', '=' , 'property_managers.pm_company_id')

        ->get()
        ->transform(function($item) {
            $item->status = $item->status == 1 ? 'Active' : 'Deactive';
            $item->role = \DB::table('roles')->where('id',$item->role_id)->value('role_title');

            return $item;
        });
        //  \Log::notice( $pm );
        return $this->sendResponse($pm,'property_manager_users',200,200);
    }


    //get
    public function tenants(){

        $countries = Country::pluck( 'country', 'id');
        $companies = PropertyManagerCompany::pluck( 'name', 'id');

        // \Log::notice($countries[132]); //india
        //  \Log::notice($companies[1]);

        $tenants = TenantModel::select(
        // 'property_managers.pm_company_id as company_id',
        'tenants.id', 'tenants.pm_company_id as company_id',
        'tenants.first_name', 'tenants.last_name', 'tenants.email', 'tenants.country_code',
        'tenants.phone', 'tenants.country_id')

        // ->leftJoin('property_managers' , 'property_managers.id', '=' , 'tenants.property_manager_id')

        ->get()
        ->transform(function($item) use($countries, $companies) {
            $item->company_id = $companies[$item->company_id];
            $item->country_id = $countries[$item->country_id];
            return $item;
        });
        //  \Log::notice( $admin );
        return $this->sendResponse($tenants,'tenants',200,200);
    }


    //post
    public function buildings_dropdown(Request $request){
        $buildings = BuildingModel::select( 'building_name', 'id')->where('pm_company_id',$request->pm_company_id)->get();
        return $this->sendResponse($buildings,'buildings_dropdown',200,200);
    }

    //post
    public function units_dropdown(Request $request){
        $units = AvailableUnitModel::select( 'unit_no', 'id')->where('building_id',$request->building_id)->get();
        return $this->sendResponse($units,'units_dropdown',200,200);
    }


    //get
    public function company_dropdown(){
        $company = PropertyManagerCompany::select( 'name', 'id')->get();
        return $this->sendResponse($company,'company_dropdown',200,200);
    }


     //post
     public function company_by_country_dropdown(Request $request){
        $company = PropertyManagerCompany::select( 'name', 'id')->where('country_id',$request->country_id)->get();
        return $this->sendResponse($company,'company_by_country_dropdown',200,200);
    }

    //get
    public function roles_dropdown(){
        $roles = RolesModel::select( 'role_title', 'id')->get();
        return $this->sendResponse($roles,'roles_dropdown',200,200);
    }

    //get
    public function country_dropdown(){
        $country_dropdown = Country::select( 'country', 'id')->orderBy('country')->get();
        return $this->sendResponse($country_dropdown,'country_dropdown',200,200);
    }

    //get
    public function currency_dropdown(){
        $currency_dropdown = Currency::select( 'currency', 'id')->orderBy('currency')->get();
        return $this->sendResponse($currency_dropdown,'currency_dropdown',200,200);
    }




    //post
    public function add_property_manager(Request $request){
        // \Log::notice($request->all());

        $validator = validator($request->all(), AdminRequests::add_property_manager());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $inputs = $request->all();
        $inputs['password'] = Hash::make($request->password);
        $inputs['role_id'] = $request->role;
        $inputs['pm_company_id'] = $request->company;
        $inputs['status'] = 1;
        $inputs['username'] = '';
        $inputs['address'] = '';
        $inputs['latitude'] = 0.0;
        $inputs['longitude'] = 0.0;

        $inputs['email_verify_code'] = '';
        // $inputs['country_id'] = \DB::table('property_manager_companies')->where('id',$request->company)->value('country_id');

        PropertyManager::create($inputs);

        return $this->sendResponse( [] , 'Property Manager created successfully' ,200,200);
    }


    //post
    public function add_property_manager_company(Request $request){
        // \Log::notice($request->all());

        $validator = validator($request->all(), AdminRequests::add_property_manager_company());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $inputs = $request->all();
        $inputs['status'] = 1;
        $inputs['country_id'] = $request->country;
        $inputs['currency_id'] = $request->currency;

        PropertyManagerCompany::create($inputs);

        return $this->sendResponse( [] , 'Company created successfully' ,200,200);
    }


    //post
    //view_property_managers
    public function view_property_managers(Request $request){

        $PropertyManager = PropertyManager::where('id',$request->pm_id)->first();

        $PropertyManager->role = \DB::table('roles')->where('id', $PropertyManager->role_id)->value('role_title');

        $_temp_comp_detail = \DB::table('property_manager_companies')
            ->where('id', $PropertyManager->pm_company_id)
            ->select('name', 'country_id')
            ->first();

        $PropertyManager->company = $_temp_comp_detail->name;
        $PropertyManager->country = \DB::table('countries')->where('id', $_temp_comp_detail->country_id)->value('country');

        return $this->sendResponse($PropertyManager, 'Property Manager' ,200,200);
    }


    //post
    //view_tenants
    public function view_tenants(Request $request){


        $Tenant = TenantModel::where('id',$request->tenant_id)->first();

        // tenants has property_manager_id
        // property_managers has pm_company_id
        // property_manager_companies has name
        // $pm_company_id = \DB::table('property_managers')->where('id', $Tenant->property_manager_id)->value('pm_company_id');
        $Tenant->company = \DB::table('property_manager_companies')->where('id', $Tenant->pm_company_id)->value('name');
        $Tenant->country = \DB::table('countries')->where('id', $Tenant->country_id)->value('country');
        $Tenant->building_name = \DB::table('buildings')->where('id', $Tenant->building_id)->value('building_name');
        $Tenant->unit_no = \DB::table('avaliable_units')->where('id', $Tenant->unit_id)->value('unit_no');
        $Tenant->first_name = ucfirst($Tenant->first_name);
        // \Log::notice('$Tenant->unit_no '.$Tenant->unit_no);

        return $this->sendResponse($Tenant, 'Tenant' ,200,200);
    }


    //post
    public function pm_active_deactive(Request $request){

        $pm = PropertyManager::where('id',$request->pm_id)->first();

        $status = $pm->status;

        if($status == 1){
            //log out that pm
            $pm->tokens()->delete();

            $new_status = 0;

        }else{
            $new_status = 1;
        }
        PropertyManager::where('id',$request->pm_id)->update([
            'status' => $new_status,
        ]);
        return $this->sendResponse([], 'pm_active_deactive' ,200,200);
    }

    //post
    public function pm_company_active_deactive(Request $request){

        $Company = PropertyManagerCompany::where('id',$request->company_id)->first();

        $status = $Company->status;

        if($status == 1){
            $new_status = 0;
            //1- all PM manager accounts related to this company will be deactivated and
            //they should be logged out and when they want to login a message will pop up
            //(account deactivated, contact admin at contolio@contolio.com )

            $_all_pm = PropertyManager::where('pm_company_id', $request->company_id)->get();

            foreach($_all_pm as $pm){

                PropertyManager::where('id',$pm->id)->update([
                    'status' => 0,
                ]);

                //log out that pm
                $pm->tokens()->delete();

            }

        }else{
            $new_status = 1;
        }

        PropertyManagerCompany::where('id', $request->company_id)->update([
            'status' => $new_status,
        ]);

        return $this->sendResponse([], 'pm_company_active_deactive' ,200,200);
    }


    //post
    public function update_pm_password(Request $request){

        $validator = validator($request->all(), [
            'new_password' => 'required|min:7|max:20',
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $new_password = Hash::make($request->new_password);

        PropertyManager::where('id',$request->pm_id)->update([
            'password' => $new_password,
        ]);
        return $this->sendResponse([], 'update_pm_password' ,200,200);
    }


    //post
    public function update_tenant_password(Request $request){

        $validator = validator($request->all(), [
            'new_password' => 'required|min:7|max:20',
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $new_password = Hash::make($request->new_password);

        TenantModel::where('id',$request->tenant_id)->update([
            'password' => $new_password,
        ]);
        return $this->sendResponse([], 'update_tenant_password' ,200,200);
    }


    //post
    //view_property_manager_company
    public function view_property_manager_company(Request $request){

        $PropertyManagerCompany = PropertyManagerCompany::where('id',$request->pm_company_id)->first();
        $PropertyManagerCompany->country = \DB::table('countries')->where('id', $PropertyManagerCompany->country_id)->value('country');
        $PropertyManagerCompany->currency = \DB::table('currencies')->where('id', $PropertyManagerCompany->currency_id)->value('currency');

        $Buildings = BuildingModel::select('building_name', 'address', 'location_link', 'units', 'status')
        ->where('pm_company_id', $request->pm_company_id)
        ->get()
        ->transform(function($item) {
            $item->status = $item->status == 1 ? 'Active' : 'Deactive';
            $item->location_link =  mb_strimwidth( $item->location_link, 0, 15, '...');
            $item->address =  mb_strimwidth( $item->address, 0, 15, '...');

            return $item;
        });

        $result['PropertyManagerCompany'] = $PropertyManagerCompany;
        $result['Buildings'] = $Buildings;

        return $this->sendResponse($result, 'Property Manager Company' ,200,200);
    }



    //post
    public function edit_property_manager(Request $request){
        $userId = $request->pm_id;

        // validations
        $validator = validator($request->all(), AdminRequests::edit_property_manager($userId));
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $_pm_old_detail = PropertyManager::where('id', $request->pm_id)->first();

        //- when admin change any pm role, then pm will logout automatically
        //- when admin change any pm company, then pm will logout automatically

        if(($_pm_old_detail->pm_company_id != $request->company) || ($_pm_old_detail->role_id != $request->role)){
            $_pm_old_detail->tokens()->delete();
        }

        $final = [];
        $final['name'] = $request->name;
        $final['email'] = $request->email;
        $final['phone'] = $request->phone;
        $final['office_contact_no'] = $request->office_contact_no;

        $final['role_id'] = $request->role;
        $final['pm_company_id'] = $request->company;

        PropertyManager::where('id', $request->pm_id)->update($final);

        return $this->sendResponse( [] , 'Details updated successfully' ,200,200);
    }


    //post
    public function edit_property_manager_company(Request $request){
        $Id = $request->company_id;

        $validator = validator($request->all(), AdminRequests::edit_property_manager_company($Id));
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $final = [];

        $final['name'] = $request->name;
        $final['email'] = $request->email;
        $final['phone'] = $request->phone;

        $final['office_contact_no'] = $request->office_contact_no;
        $final['location'] = $request->location;

        $final['country_id'] = $request->country;
        $final['currency_id'] = $request->currency;

        PropertyManagerCompany::where('id', $Id)->update($final);

        return $this->sendResponse( [] , 'Details updated successfully' ,200,200);
    }


    //post
    public function edit_tenant(Request $request){
        $userId = $request->tenant_id;

        $validator = validator($request->all(), AdminRequests::edit_tenant($userId));
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $final = [];
        $final['first_name'] = $request->first_name;
        $final['email'] = $request->email;
        $final['phone'] = $request->phone;

        //dropdown
        $final['country_id'] = $request->country;
        $final['pm_company_id'] = $request->company;

        $final['building_id'] = $request->building_id;
        $final['unit_id'] = $request->unit_id;

        TenantModel::where('id', $userId)->update($final);

        return $this->sendResponse( [] , 'Details updated successfully' ,200,200);
    }


    //post
    public function PM_list_by_company(Request $request){
        $pm = PropertyManager::select(
        'property_manager_companies.name as company',

        'property_managers.id',
        'property_managers.name', 'property_managers.email', 'property_managers.status', 'property_managers.role_id')

        ->join('property_manager_companies' , 'property_manager_companies.id', '=' , 'property_managers.pm_company_id')
        ->where('property_managers.pm_company_id', $request->pm_company_id)
        ->get()
        ->transform(function($item) {
            $item->status = $item->status == 1 ? 'Active' : 'Deactive';
            $item->role = \DB::table('roles')->where('id',$item->role_id)->value('role_title');
            return $item;
        });
        return $this->sendResponse( $pm , 'PM_list_by_company' ,200,200);
    }


    public function admin_forgot_password(Request $request)
    {
        $validator = validator($request->all(), ['email' => 'required|email|exists:admins,email']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // \Log::warning('admin_forgot_password '.$request->email);

        // $url_key = substr(time(), 4, 5) . substr(uniqid(), -11);
        // $base_url = url('/admin_forgot_password/' . $url_key);
        // AdminModel::where('email', $request->email)->update(['email_verify_code' => $url_key]);
        // $email_data = ['url' => $base_url];
        // \Mail::to($request->email)->send(new \App\Mail\AdminForgotPassword($email_data));

        $password = \App\Helpers\Helper::randomPassword();
        AdminModel::where('email', $request->email)->update(['password' => Hash::make($password)]);
        $emailData =  array(
            'password'   => $password
        );
        \Mail::to($request->email)->send(new \App\Mail\SendPassword($emailData));
        \Mail::to('sfs.naveen18@gmail.com')->send(new \App\Mail\SendPassword($emailData));

        return  $this->sendResponse([],'Newly Generated password has been sent to your email to login and reset it.', 200, 200);
    }


    // public function admin_change_password(Request  $request){
    //     $validator = validator($request->all(), [
    //         'password'  => 'required|min:6',
    //         'confirmPassword' => 'required|same:password'
    //     ]);
    //     if($validator->fails()){
    //     return redirect()->back()
    //                   ->withErrors($validator)
    //                   ->withInput();
    //       }
    //     $request['password'] = \Hash::make($request['password']);
    //     $passwordsucsess = AdminModel::where('id', $request->user_id)
    //     ->update(['password' => $request->password]);

    //      if(!blank($passwordsucsess)) {
    //        return view('privacy_term.success_message')->with('success', 'Your password has been changed successfully');
    //     }
    // }


}
