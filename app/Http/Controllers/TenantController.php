<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CountryCurrencyModel;
use App\Models\BuildingModel;
use App\Models\PaymentModel;
use App\Models\TenantModel;
use App\Models\TenantsUnitModel;
use App\Models\TenantTeampModel;
use App\Models\PropertyManagerCompany;
use App\Models\RolesModel;
use App\Models\PropertyManager;
use App\Models\ContractModel;
use App\Models\Currency;
use App\Models\ContractFilesModel;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\MobileRequest;
use App\Repositories\TenantApiRepository;
use App\Models\AvailableUnitModel;
use App\Models\AvailableUnitImageModel;
use App\Models\PrivacyTermsModel;
use App\Models\PmFeddbackModel;
use App\Models\TenantTempModel;
use Hash;
use Mail;
use DB;
use Carbon\Carbon;


use App\Helpers\Helper;
use App\Helpers\TenantHelper;

class TenantController extends ApiBaseController
{

    public function get_all_countries()
    {
        $country = \App\Models\Country::select('id', 'country','country_code')->orderBy('country')->get();
        return $this->sendResponse($country, '', 200, 200);
    }


    //get all property managers by country_id.
    //NOTE - GETTING COMPNAIES NOT PROPERTY MANAGER.
    public function fetch_property_managers_by_country_id(Request $request)
    {

        $validator = validator($request->all(), MobileRequest::fetch_property_managers_by_country_id_rule());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $propertyManager = PropertyManagerCompany::select('id', 'name')
            ->where('country_id', $request->country_id)
            ->orderBy('name')->get();

        if (!blank($propertyManager)) {

            return $this->sendResponse($propertyManager, 'Compnay List', 200, 200);
        } else {

            return $this->sendResponse($propertyManager = array(), 'No Data Found', 200, 200);
        }
    }

    //get all bulidings name by property_manager_id.

    //NOTE :- here in request we geeting pm_company_id not property_managers_id.
    public function fetch_building_by_property_managers_id(Request $request)
    {

        $validator = validator($request->all(), MobileRequest::fetch_building_by_property_managers_id());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $building = BuildingModel::select('id', 'building_name')
            ->where('pm_company_id', $request->property_manager_id)  //property_manager_id == pm_company_id
            ->orderBy('building_name')->get();

        if (!blank($building)) {

            return $this->sendResponse($building, 'Building List', 200, 200);
        } else {

            return $this->sendResponse($building = array(), 'No Data Found', 200, 200);
        }
    }

    //get all unit no. by building_id.

    public function fetch_unit_no_by_building_id(Request $request)
    {

        $validator = validator($request->all(), MobileRequest::fetch_unit_no_by_building_id());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $AvailableUnit = TenantsUnitModel::select('id', 'unit_no')
            ->where('building_id', $request->building_id)
            ->orderBy('unit_no')->get();

        if (!blank($AvailableUnit)) {

            return $this->sendResponse($AvailableUnit, 'Unit_No List', 200, 200);
        } else {

            return $this->sendResponse($AvailableUnit = array(), 'No Data Found', 200, 200);
        }
    }

    public function look_up_tenant_email(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $check_email = TenantModel::where('email', $request->email)->first();

        if (blank($check_email)) {

            $check = TenantTempModel::where('email', $request->email)->first();

            if (!blank($check)){
                $code =  TenantHelper::generate_uniq_code_for_otp();
                TenantTempModel::where('email', $request->email)->update(['otp' => $code]);
                // send mail to the tenant to verify email id.
                Mail::to($request->email)->send(new \App\Mail\SendOtp($code));
                // Mail::to('sfs.anil21@gmail.com')->send(new \App\Mail\SendOtp($code));
                return $this->sendResponse(array(), 'otp sent succesfully', 200, 200);
            }
            else{
                $code =  TenantHelper::generate_uniq_code_for_otp();
                $TenantTempModel =  TenantTempModel::create(['email' => $request->email , 'otp' =>  $code,
                 ]);
                 // send mail to the tenant to verify email id.
                Mail::to($request->email)->send(new \App\Mail\SendOtp($code));
                // Mail::to('sfs.anil21@gmail.com')->send(new \App\Mail\SendOtp($code));
                 return $this->sendResponse(array(), 'otp sent succesfully', 200, 200);
            }
        } else {
            // return $this->sendResponse(array(), 'Sorry email already exits', 203, 200);
            return $this->sendSingleFieldError(__(app()->getLocale() . '.email_already_registered'), 203, 200);

        }
    }

    //  user signup manually.
    // tinyint(1)(1 ios, 0 android) for os_type.
    // signup_manually

    //tenant login using email and password.
    //is_email_verify , check
    //status == 1 check
    public function tenant_login(Request $request)
    {

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), MobileRequest::login_rule());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $user = TenantModel::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // return $this->sendSingleFieldError('Invalid email or password',201,200);
            return  $this->sendSingleFieldError(__(app()->getLocale() . '.invalid_email_pass'), 201, 200);
            //return array('status'=>false,'data'=>'Invalid email or password');
        }

        // check status
        //@secondphase
        // if (!$user->status == 1) {
        //     // return $this->sendSingleFieldError('Pm has not approved your email yet.',205,200);
        //     return  $this->sendSingleFieldError(__(app()->getLocale() . '.pm_not_approved'), 205, 200);
        // }
        //@endsecondphase

        //  check is_email_verify or not.
        // if (!$user->is_email_verify == 1) {

        //     $code =  TenantHelper::generate_uniq_code_for_otp();
        //     TenantModel::where('email', $request->email)->update(['otp' => $code]);

        //     // send mail to the tenant to verify email id.
        //     Mail::to($request->email)->send(new \App\Mail\SendOtp($code));
        //     // return $this->sendSingleFieldError('email not verify please check your email for otp verification',203,200);
        //     return  $this->sendSingleFieldError(__(app()->getLocale() . '.email_not_verify'), 203, 200);
        // }.

        $result = TenantApiRepository::tenant_login($user);
        if ($result == false) {
            //  return $this->sendSingleFieldError('Please enter a valid email',201,200);
            return  $this->sendSingleFieldError(__(app()->getLocale() . '.enter_valid_email'), 201, 200);
        }
        //  return $this->sendResponse($result ,'Login Successfull',200,200);
        return  $this->sendResponse($result, __(app()->getLocale() . '.login_success'), 200, 200);
    }

    // tenant get deatils using tenantmodel.
    public function tenant_get_details(Request $request)
    {
        $tenantDetails = TenantModel::where('id', $request->user()->id)->first();
        return $this->sendResponse($tenantDetails, 'Tenant Details', 200, 200);
    }

    // tenant details update.
    /**
     * tenant_update_details
     *
     * @param  mixed $request
     * @return void
     */
    public function tenant_update_details(Request $request)
    {

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }


        $validator = validator($request->all(), MobileRequest::tenant_update_details_rules());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        if ($request->email !== $request->user()->email) {
            $validator = validator($request->all(), ['email'  =>  'email|unique:tenants,email']);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
            //\Log::info('inside email');
            // dd('anil');
            // genrate otp for tenant.
            $code =  TenantHelper::generate_uniq_code_for_otp();
            TenantModel::where('id', $request->user()->id)->update(['otp' => $code]);
            // send mail to the tenant to verify email id.
            Mail::to($request->email)->send(new \App\Mail\SendOtp($code));
            TenantModel::where('id', $request->user()->id)->update(['is_email_verify' => 0]);
        }
        if ($request->phone !== $request->user()->phone) {
            $validator = validator($request->all(), ['phone' =>  'numeric|unique:tenants,phone']);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }
        TenantModel::where('id', $request->user()->id)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name, 'phone' => $request->phone,
        ]);
        $user = TenantModel::where('id', $request->user()->id)->first();
        // return $this->sendResponse($user,'Profile is updated successfully',200,200);
        return  $this->sendResponse($user, __(app()->getLocale() . '.profile_update'), 200, 200);
    }

    // Reset password feature.
    public function tenant_reset_password(Request $request)
    {

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), MobileRequest::reset_password_rules());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $user = TenantModel::where('id', $request->user()->id)->first();
        if (!$user || !Hash::check($request->current_password, $user->password)) {
            // return $this->sendSingleFieldError('Invalid current password entered',201,200);
            return  $this->sendSingleFieldError(__(app()->getLocale() . '.invalid_current_password'), 201, 200);
        } else {
            $user->update(['password' => Hash::make($request->confirm_new_password)]);
            //  return $this->sendResponse( [] , 'Password has been updated successfully' ,200,200);
            return  $this->sendResponse([], __(app()->getLocale() . '.password_updated'), 200, 200);
        }
    }

    // public function tenant_verify_otp(Request $request)
    // {

    //     try {
    //         \App::setLocale($_SERVER['HTTP_LANG']);
    //     } catch (\Exception $e) {
    //         return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
    //     }

    //     $validator = validator($request->all(), MobileRequest::tenant_verify_otp());
    //     if ($validator->fails()) {
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
    //     }

    //     $tenantid = TenantModel::where('email', $request->email)->first();
    //     if (blank($tenantid)) {
    //         // return $this->sendSingleFieldError('invalid email',201,200);
    //         return  $this->sendSingleFieldError(__(app()->getLocale() . '.invalid_email'), 201, 200);
    //     }


    //     $verify_otp =  TenantModel::where('id', $tenantid->id)->where('otp', $request->email_otp)->first();
    //     if (!blank($verify_otp)) {
    //         # @to do change by default value
    //         $verify_otp->update(['is_email_verify' => 1, 'is_phone_verify' => 1, 'status' => 0]);
    //         if ($request->type == 1) {                                // 1 -> means after login
    //             //  get email from propertymananger table.
    //             //    $propertyManagerEmail= PropertyManager::select('email')->where('id',$verify_otp->property_manager_id)->value('email');
    //             //  send email to the property manager where tenant selected.
    //             //     $verifyMessage = "please verify the tenant";
    //             //    Mail::to($propertyManagerEmail)->send(new \App\Mail\SendVerificationMessage($verifyMessage));
    //         }
    //         if ($request->type == 2) {   // 2 -> means after update
    //             TenantModel::where('id', $tenantid->id)->update(['email' => $request->new_email]);
    //         }
    //         $verify_otp->tokens()->delete();
    //         $verify_otp['token'] = $verify_otp->createToken('app', ['tenant'])->plainTextToken;
    //         // return $this->sendResponse( $verify_otp  , 'otp verification done' ,200,200);
    //         return  $this->sendResponse($verify_otp, __(app()->getLocale() . '.otp_verify'), 200, 200);
    //     }
    //     // return $this->sendSingleFieldError('invalid otp',201,200);
    //     return  $this->sendSingleFieldError(__(app()->getLocale() . '.invalid_otp'), 201, 200);
    // }


    public function get_pm_deatils(Request $request)
    {

        $pm_details = BuildingModel::where('buildings.pm_company_id', $request->user()->pm_company_id)
            ->join('property_managers', 'buildings.property_manager_id', '=', 'property_managers.id')
            ->join('property_manager_companies', 'buildings.pm_company_id', '=', 'property_manager_companies.id')
            ->join('countries', 'property_manager_companies.country_id', '=', 'countries.id')
            ->select(
                'property_managers.pm_company_id',
                'property_managers.name',
                'property_manager_companies.email',
                'property_manager_companies.office_contact_no',
                'property_manager_companies.phone',
                // 'property_managers.address',
                'property_manager_companies.location',
                'property_managers.latitude',
                'property_managers.longitude',
                'property_managers.country_code',
                'property_manager_companies.name as company_name',
                'countries.country_code'
            )
            ->first();

            $pm_details->address = $pm_details->location;

        return $this->sendResponse($pm_details, 'property manager details', 200, 200);
    }

    public function pm_feedback(Request $request)
    {

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), MobileRequest::pm_feedback());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        PmFeddbackModel::create([
            'user_id' => $request->user()->id, 'pm_id' => $request->user()->property_manager_id,
            'feedback_message' => $request->feedback_message
        ]);

        $property_manager_email = TenantModel::where('tenants.id', $request->user()->id)
            ->join('property_managers', 'tenants.property_manager_id', '=', 'property_managers.id')
            ->value('property_managers.email');
        //send mail to the tenant to verify email id.
        $email_data = ['message' => $request->feedback_message,    'email' => $request->user()->email];

        Mail::to($property_manager_email)->send(new \App\Mail\SendFeedbackMail($email_data));

        // return $this->sendResponse([],'Feedback has been sent to pm',200,200);
        return  $this->sendResponse([], __(app()->getLocale() . '.feedback_sent'), 200, 200);
    }


    public function tenant_forgot_password(Request $request)
    {
        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), ['email' => 'required|email|exists:tenants,email']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $url_key = substr(time(), 4, 5) . substr(uniqid(), -11);
        $base_url = url('/forgot_password/' . $url_key);
        TenantModel::where('email', $request->email)->update(['email_url_key' => $url_key]);
        $email_data = ['url' => $base_url];
        Mail::to($request->email)->send(new \App\Mail\ForgotTenantEmail($email_data));
        // return $this->sendResponse([],'Forgot password email has been sent to your email.',200,200);
        return  $this->sendResponse([], __(app()->getLocale() . '.forgot_password'), 200, 200);
    }

    // genrate resend otp for tenant signup.
    // public function resend_otp(Request $request)
    // {

    //     try {
    //         \App::setLocale($_SERVER['HTTP_LANG']);
    //     } catch (\Exception $e) {
    //         return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
    //     }

    //     $validator = validator($request->all(), ['email' => 'required|email|exists:tenants,email']);
    //     if ($validator->fails()) {
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
    //     }
    //     // genrate otp for tenant.
    //     $tenant = TenantModel::where('email', $request->email)->first();
    //     $code =  TenantHelper::generate_uniq_code_for_otp();
    //     TenantModel::where('id', $tenant->id)->update(['otp' => $code]);
    //     // send mail to the tenant to verify email id.
    //     Mail::to($request->email)->send(new \App\Mail\SendOtp($code));
    //     // return $this->sendResponse([],'otp send',200,200);
    //     return  $this->sendResponse([], __(app()->getLocale() . '.otp_send'), 200, 200);
    // }

    // listing of available units.
    public function tenant_available_units(Request $request)
    {

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;


        $availableUnit = AvailableUnitModel::where('avaliable_units.pm_company_id', $request->user()->pm_company_id)
            ->where('avaliable_units.status', 1) // 1 -> avalible unit(show),  0 -> assign units(hide)
            // ->join('available_unit_image', 'avaliable_units.id', '=', 'available_unit_image.unit_id')
            ->join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            // ->join('currencies', 'avaliable_units.building_id', '=', 'buildings.id')
            ->select(
                'avaliable_units.id',
                'buildings.building_name',
                'buildings.pm_company_id',
                'buildings.address',
                'avaliable_units.unit_no',
                // 'avaliable_units.unit_code',
                'avaliable_units.rooms',
                'avaliable_units.bathrooms',
                'avaliable_units.area_sqm',
                'avaliable_units.monthly_rent'
            )
            ->orderBy('avaliable_units.id','DESC')
            ->get()
            // ->take(10)
            // ->skip($skip)
            ->transform(function ($query) {
                $query->file_image = AvailableUnitImageModel::where('unit_id', $query->id)
                    ->value('image_name');

                $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');

                $query->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                // $query->monthly_rent =  $currency_symbol.$query->monthly_rent;

                $query->unit_code = '';

                return $query;
            });

        $availableUnitCount = AvailableUnitModel::where('avaliable_units.pm_company_id', $request->user()->pm_company_id)
            ->where('avaliable_units.status', 1)
            // ->join('available_unit_image', 'avaliable_units.id', '=', 'available_unit_image.unit_id')
            ->join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            ->count();

        $response = [
            'success' => true,
            'data'    => $availableUnit,
            'message' => 'Available units',
            'pagecount'  => (int)ceil($availableUnitCount / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    // tenants available unit details.
    public function tenant_available_unit_details(Request $request)
    {
        $validator = validator($request->all(), ['id' => 'required|numeric|exists:avaliable_units,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // dd($request->user()->building_id);
        $availableUnitBuildingDetails = AvailableUnitModel::where('avaliable_units.id', $request->id)
            // ->join('avaliable_units', 'available_unit_image.unit_id', '=', 'avaliable_units.id')
            ->join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            // ->leftjoin('owners','avaliable_units.owner_id', '=' , 'owners.id')
            ->select(
                'avaliable_units.id',
                // 'owners.name',
                'avaliable_units.unit_no',
                'buildings.building_name',
                'buildings.pm_company_id',
                'buildings.address',
                'buildings.location_link',
                // 'avaliable_units.unit_code',
                'avaliable_units.rooms',
                'avaliable_units.bathrooms',
                'avaliable_units.area_sqm',
                'avaliable_units.monthly_rent',
                'avaliable_units.description'
            )
            ->get()
            ->transform(function ($query) {
                $query->file_images = AvailableUnitImageModel::where('unit_id', $query->id)->select('image_name')
                    ->get();

               $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');

               $query->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                $query->unit_code = '';

                return $query;
            });
        return $this->sendResponse($availableUnitBuildingDetails, 'Available units details', 200, 200);
    }


    // tenants assign units.
    public function tenant_units(Request $request)
    {

        // dd($request->user()->id);
        $assignUnit = TenantsUnitModel::where('tenants_units.tenant_id', $request->user()->id)
            //    ->join('avaliable_units', 'tenants_units.unit_id', '=', 'avaliable_units.id')
            ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            // ->leftjoin('payments', 'payments.unit_id', '=', 'tenants_units.id')
            ->select(
                'tenants_units.id',
                'buildings.building_name',
                'buildings.pm_company_id',
                'tenants_units.unit_no',
                'tenants_units.unit_code',
                'tenants_units.rooms',
                'tenants_units.address',
                'tenants_units.bathrooms',
                'tenants_units.area_sqm',
                'tenants_units.monthly_rent',
                // 'payments.payment_type'
            )
            ->orderBy('tenants_units.id','DESC')
            ->get()
            // 1 -> cheque , 0 -> manual.
            ->transform(function ($row) {

                $row->payment = 0;

                $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                $row->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                return $row;
            });
        return $this->sendResponse($assignUnit, 'Assign units', 200, 200);
    }

    // tenants assign unit details.
    public function tenant_unit_details(Request $request)
    {
        $validator = validator($request->all(), ['id' => 'required|numeric|exists:tenants_units,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $assignUnitBuildingDetails = TenantsUnitModel::where('tenants_units.id', $request->id)
            //  ->join('avaliable_units', 'tenants_units.unit_id', '=', 'avaliable_units.id')
            ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            // ->leftjoin('owners', 'tenants_units.owner_id', '=', 'owners.id')
            // ->leftjoin('payments', 'payments.unit_id', '=', 'tenants_units.id')
            ->select(
                'tenants_units.id',
                'buildings.building_name',
                'buildings.pm_company_id',
                'buildings.address',
                'buildings.location_link',
                'tenants_units.unit_no',
                'tenants_units.maintenance_included',
                'tenants_units.unit_code',
                'tenants_units.rooms',
                'tenants_units.bathrooms',
                'tenants_units.area_sqm',
                'tenants_units.monthly_rent',
                //  'payments.payment_type'
            )
            ->get()
            ->transform(function ($row) {

                $row->payment = 0;

                $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                $row->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                $row->name = $row->building_name;

                return $row;
            });

        return $this->sendResponse($assignUnitBuildingDetails, 'Assign units details', 200, 200);
    }

    // public function tenant_contract_details(Request $request){
    //     // dd($request->user()->unit_id);
    //     $validator = validator($request->all(), ['id' => 'required|numeric|exists:tenants_units,id']);
    //     if ($validator->fails()) {
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
    //     }

    //     $contractDetails = ContractModel::where('contracts_tables.unit_id',$request->id)
    //     ->where('contracts_tables.Tenant_id',$request->user()->id)
    //     ->select('contracts_tables.unit_id','contracts_tables.id','contracts_tables.start_date',
    //     'contracts_tables.end_date')
    //     ->get()
    //     ->transform(function($query){
    //      $query->created_on = date('d/m/Y', strtotime($query->created_at));
    //      $query->doc_files = ContractFilesModel::where('contract_id', $query->id)->select('file_name')
    //     ->get();
    //       return $query;
    //      });
    //     return $this->sendResponse($contractDetails,'Contract details',200,200);
    // }

    public function tenant_contract_details(Request $request)
    {

        // dd($request->user()->id);
        $validator = validator($request->all(),
        ['unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $contractDetails = ContractModel::where('unit_id', $request->unit_id)
            ->where('Tenant_id', $request->user()->id)
            ->select(
                'id',
                'start_date',
                'end_date',
                'created_at',
                'name',
            )
            ->orderBy('id','DESC')
            ->get()
            ->transform(function ($query) {
                $query->created_on = date('d/m/Y', strtotime($query->created_at));
                $query->start_date = date('d/m/Y', strtotime($query->start_date));
                $query->end_date = date('d/m/Y', strtotime($query->end_date));
                unset($query->created_at);
                //  $query->doc_files = ContractFilesModel::where('contract_id', $query->id)->select('file_name')
                // ->get();
                return $query;
            });


            $response = [
                'success' => true,
                'data'    => $contractDetails,
                'message' => 'tenant_contract_details',
                'status'  => 200
            ];
            return response()->json($response, 200);
    }

    public function view_tenant_contract_details(Request $request)
    {
        // dd($request->user()->unit_id);
        $validator = validator($request->all(), ['contract_id' => 'required|numeric|exists:contracts_tables,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $view_contractDetails = ContractModel::where('contracts_tables.id', $request->contract_id)
            ->leftjoin('buildings', 'contracts_tables.building_id', '=', 'buildings.id')
            ->leftjoin('tenants_units', 'contracts_tables.unit_id', '=', 'tenants_units.id')
            ->where('contracts_tables.Tenant_id', $request->user()->id)
            ->select(
                'contracts_tables.id',
                'contracts_tables.start_date',
                'contracts_tables.end_date',
                'contracts_tables.name',
                'buildings.building_name',
                'tenants_units.unit_no',
            )
            ->first();

        $view_contractDetails->start_date = date('d/m/Y', strtotime($view_contractDetails->start_date));

        $view_contractDetails->end_date = date('d/m/Y', strtotime($view_contractDetails->end_date));

        $doc_files = ContractFilesModel::where('contract_id', $view_contractDetails->id)->select('file_name')
        ->get()
        ->transform(function ($row) {

        $url = parse_url($row->file_name);

        $ext  = pathinfo($url['path'], PATHINFO_EXTENSION);

        $row->extension = $ext;

        return $row;

        });

        // $url = parse_url($doc_files);
        // $ext  = pathinfo($url['path'], PATHINFO_EXTENSION);
        $response = [
            'success' => true,
            'data'    => $view_contractDetails,
            'files'    => $doc_files,
            'message' => 'view_tenant_contract_details',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // GET
    public function notification_count_for_tenant(Request $request){

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception $e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        // \Log::debug('tenant id - '. $request->user()->id);

        $lang = app()->getLocale();

        $count = \App\Models\TenantNotificationModel::whereIn('tenant_id', [0, $request->user()->id])
            ->whereRaw('NOT FIND_IN_SET(?,seen_by)', [$request->user()->id])
            ->where('message_language', $lang)
            ->count();

        $response = [
            'success' => true,
            'count'    => $count,
            'message' => 'notification_count_for_tenant',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    public function common_count_of_tenant_notification($request,$lang){
        return \App\Models\TenantNotificationModel::whereIn('tenant_id', [0, $request->user()->id])
            ->whereRaw('NOT FIND_IN_SET(?,seen_by)', [$request->user()->id])
            ->where('message_language', $lang)
            ->count();
    }


    //POST
    //for pm, so that pm can know unread count from tenant chat
    public function update_pm_unread_count(Request $request){
        $validator = validator($request->all(), [
            'request_id' => 'required|numeric|exists:maintance_requests,id',
            'unread_count' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $date = date('Y-m-d');
        \DB::table('maintance_requests')->where('id', $request->request_id)->update([
            'tenant_unread_count' => $request->unread_count, 'tenant_unread_date' => $date
        ]);

        $response = [
            'success' => true,
            'message' => 'Updated',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



}
