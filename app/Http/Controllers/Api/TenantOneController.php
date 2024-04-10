<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantModel;
use App\Models\TenantsUnitModel;
use App\Models\BuildingModel;
use App\Models\Country;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\PmRequest;
use App\Helpers\Helper;
use Hash;
use Mail;
use Illuminate\Support\Carbon;




class TenantOneController extends ApiBaseController
{
    // POST
    public function unlinked_tenant_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), ['page' => 'required|numeric']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        // \DB::enableQueryLog();
        $tenants_details =  \DB::table('tenants')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'country_code', 'status')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            // ->whereIn('tenants.status', [0, 2, 3])
            //@secondphase
            ->whereIn('tenants.status',[1,3])
            ->whereNotIn('id', function ($query) use ($request) {
                //2 get all tenant id of linked tenants
                $query->select('tenants.id')
                    ->from('tenants')
                    ->join('tenants_units', 'tenants.id', 'tenants_units.tenant_id')
                    ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                    // ->whereIn('tenants.status', [0, 2, 3])
                    //@secondphase
                    ->whereIn('tenants.status', [1,3])
                    ->get();
            })
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function ($query) {
                // $query->requested_date = date('d M Y', strtotime($query->created_at));
                $query->tenant_name = $query->first_name . ' ' . $query->last_name;
                $status_int = $query->status;

                switch ($status_int) {
                    //@secondphase
                    // case 0:
                    //     $status = 'Pending Approval';
                    //     break;
                    // case 2:
                    //     $status = 'Declined';
                    //     break;
                    case 1:
                        $status = 'Approved';
                        break;
                    case 3:
                        $status = 'Disconnected';
                        break;
                    
                }

                $query->status = $status;

                return $query;
            });

        $tenant_count = \DB::table('tenants')
            ->select('id')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            // ->whereIn('tenants.status', [0, 2, 3])
            //@secondphase
            ->whereIn('tenants.status', [1,3])
            ->whereNotIn('id', function ($query) use ($request) {
                //2 get all tenant id of linked tenants
                $query->select('tenants.id')
                    ->from('tenants')
                    ->join('tenants_units', 'tenants.id', 'tenants_units.tenant_id')
                    ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                    // ->whereIn('tenants.status', [0, 2, 3])
                    //@secondphase
                    ->whereIn('tenants.status', [1,3])
                    ->get();
            })
            ->count();

            // dd($tenant_count);

        $response = [
            'success' => true,
            'data'    => $tenants_details,
            'message' => 'unlinked_tenant_list_by_company_id',
            'pagecount'  => (int)ceil($tenant_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    // POST
    //PM
    // tenant table status
    // 0- Pending Approval
    // 1- Approved or linked
    // 2- Declined
    // 3- Disconnected or unlinked
    //
    public function tenant_request_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $tenants_details_query = TenantModel::join('tenants_units', 'tenants.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants.building_id', '=', 'buildings.id')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id);

        $tenants_details_query->where('tenants.status', 0);

        $tenants_details = $tenants_details_query->select(
                'tenants.id',
                'tenants.status',
                'tenants.first_name',
                'tenants.last_name',
                'tenants.country_code',
                'tenants.phone',
                'tenants.email',
                'buildings.building_name',
                'tenants_units.unit_no',
                'tenants_units.id as tenant_unit_id',
                'buildings.id as buildings_id',
            )
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function ($query) {
                $query->tenant_name = $query->first_name . ' ' . $query->last_name;
                $query->status = $query->status == 0 ? 'Pending Approval' : 'Approved';
                return $query;
            });

        $tenant_count_query = TenantModel::join('tenants_units', 'tenants.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants.building_id', '=', 'buildings.id');

        $tenant_count_query->where('tenants.status', 0);

        $tenant_count = $tenant_count_query->where('tenants.pm_company_id', $request->user()->pm_company_id)->count();

        $response = [
            'success' => true,
            'data'    => $tenants_details,
            'message' => 'tenant_request_list_by_company_id',
            'pagecount'  => (int)ceil($tenant_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    //PM
    public function view_tenant_request_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), ['tenant_id' => 'required|numeric|exists:tenants,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $tenant_details = TenantModel::where('id', $request->tenant_id)->select(
            'first_name',
            'id as tenant_id',
            'last_name',
            'country_code',
            'phone',
            'email',
            'country_id',
            'tenant_code',
            'unit_id',
            'building_id',
            'status'
        )->first();

        if ($tenant_details->status != 0) {
            return $this->sendSingleFieldError('Sorry it is not a valid tenant request', 201, 200);
        }

        $_country_name = \DB::table('countries')->where('id', $tenant_details->country_id)->value('country');

        $tenant_details->country_name = !blank($_country_name) ? $_country_name : '';

        $tenant_details->tenant_name = blank($tenant_details->first_name) ? '' : $tenant_details->first_name . ' ' . $tenant_details->last_name;

        $_unit_details = \DB::table('tenants_units')
        ->where('id', $tenant_details->unit_id)
        ->value('unit_no');

        $tenant_details->unit_details = !blank($_unit_details) ? $_unit_details : '';

        $temp_build = \DB::table('buildings')
        ->where('id', $tenant_details->building_id)
        ->select('address','building_name')
        ->first();

        $tenant_details->building_name = !blank($temp_build) ? $temp_build->building_name : '';
        $tenant_details->address = !blank($temp_build) ? $temp_build->address : '';

        $response = [
            'success' => true,
            'tenant_details' => $tenant_details,
            'message' => 'view_tenant_request_by_tenant_id',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



    // POST
    //PM
    public function linked_tenant_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), ['page' => 'required|numeric']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $tenants_details = TenantModel::join('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.status', 1)
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            ->select(
                'tenants.id',
                'tenants.first_name',
                'tenants.last_name',
                'tenants.country_code',
                'tenants.phone',
                'tenants.email',
                'buildings.building_name',
                'tenants_units.unit_no',
                'tenants_units.id as tenant_unit_id',
                'buildings.id as buildings_id',
            )
            ->take(10)
            ->skip($skip)
            ->get();
        // dd($tenants_details);

        $tenant_count = TenantModel::join('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.status', 1)
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            ->count();

            // dd($tenant_count);

        $response = [
            'success' => true,
            'data'    => $tenants_details,
            'message' => 'linked_tenant_list_by_company_id',
            'pagecount'  => (int)ceil($tenant_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    // POST
    // PM
    // 0 pending approval
    // 1 approved
    // 2 declined
    // 3 Disconnected
    // if tenant approved and not linked then unit and building will not show
    public function all_tenant_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            //@secondphase
            // 'filter_by_status' => 'required|in:0,1,2,3,5',//5 all
            'filter_by_status' => 'required|in:1,3,5',//5 all

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $tenants_details_query = TenantModel::leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id);

        if($request->filter_by_status != 5){
            $tenants_details_query->where('tenants.status', $request->filter_by_status);
        }else{
            //@secondphase
            $tenants_details_query->whereIn('tenants.status', [3,1]);
        }

        $tenants_details = $tenants_details_query->select(
                'tenants.id',
                'tenants.status',
                'tenants.first_name',
                'tenants.last_name',
                'tenants.country_code',
                'tenants.phone',
                'tenants.email',
                'buildings.building_name',
                'tenants_units.unit_no',
                'tenants_units.id as tenant_unit_id',
                'buildings.id as buildings_id',
            )
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function ($query) {
                $query->tenant_name = $query->first_name . ' ' . $query->last_name;

                $status_int = $query->status;

                switch ($status_int) {
                    //@secondphase
                    // case 0:
                    //     $status = 'Pending Approval';
                    //     break;
                    case 1:
                        $status = 'Approved';
                        break;
                    // case 2:
                    //     $status = 'Declined';
                    //     break;
                    case 3:
                        $status = 'Disconnected';
                        break;
                }

                $query->status = $status;

                return $query;
            });


            if($request->filter_by_status != 5){
                $tenant_count = TenantModel::leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
                ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                ->where('tenants.status', $request->filter_by_status)
                ->count();
            }else{
                $tenant_count = TenantModel::leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
                ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                ->whereIn('tenants.status', [1,3])
                ->count();
            }


        $response = [
            'success' => true,
            'data'    => $tenants_details,
            'message' => 'all_tenant_list_by_company_id',
            'pagecount'  => (int)ceil($tenant_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // POST
    public function add_tenant(Request $request)
    {
        // \Log::info($request->all());
        // dd($request->user()->id);
        $validator = validator($request->all(), PmRequest::add_tenant());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        // case 1
        if (\DB::table('tenants')
            ->where('email', $request->email)
            //@secondphase
            // ->whereIn('status', [0, 2, 3])
            ->where('status', 3)
            ->where('pm_company_id', '!=', $request->user()->pm_company_id)
            ->first()
        ){
            TenantModel::where('email', $request->email)
                ->update(['pm_company_id' => $request->user()->pm_company_id, 'status' => 1]);
            // return $this->sendResponse([], 'tenant added successfully.', 200, 200);
            return $this->sendResponse([], __(app()->getLocale().'.tenant_added'), 200, 200);
        }

        // case 2
        if (\DB::table('tenants')
            ->where('email', $request->email)
            ->where('pm_company_id', '!=', 0)
            ->where('pm_company_id', '!=', $request->user()->pm_company_id)->first()
        ) {
            // return $this->sendSingleFieldError('Tenant can not be added, the tenant is associated with another property management company', 201, 200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.tenant_cannot_added'), 201, 200);

        }

        // case 3
        if (\DB::table('tenants')
            ->where('email', $request->email)
            ->where('pm_company_id', $request->user()->pm_company_id)
            ->where('pm_company_id', '!=', 0)
            ->first()
        ) {
            // return $this->sendSingleFieldError('Tenant already available, and associated with your company', 201, 200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.tenant_already_available'), 201, 200);

        }

        $validator = validator($request->all(), PmRequest::add_tenant_email_unique());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // get country_id from countries table.
        $country_id = \DB::table('countries')->where('country_code', $request->country_code)->value('id');

        $tenant = \App\models\TenantModel::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'pm_company_id' => $request->user()->pm_company_id,
            'property_manager_id' => $request->user()->id,
            'building_id' => 0,
            'unit_id' => 0,
            'email' => $request->email,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'country_id' => $country_id,
            'language' => '',
            'password' => '',
            'address' => '',
            'os_type' => 1,
            'otp' => 0,
            'status' => 1,
            'is_email_verify' => 1,
            'is_phone_verify' => 0,
            'email_url_key' => 0,
            'tenant_code' => ''
        ]);

        $inputs = [];
        $inputs['tenant_name'] = $tenant->first_name . ' ' . $tenant->last_name;
        $inputs['pm_company_name'] = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->value('name');
        $inputs['tenant_email'] = $request->email;
        $inputs['password'] =  Helper::generate_uniq_code($tenant->id);
        try {
            Mail::to($request->email)->send(new \App\Mail\PmGenrateTenantPassword($inputs));
        } catch (\Exception $e) {
            Log::error('---------add_tenant------------------- ' . $e);
        }

        $passwordencypt = Hash::make($inputs['password']);

        TenantModel::where('id', $tenant->id)->update(['password' => $passwordencypt]);

        $tenant_code =  Helper::generate_uniq_code($tenant->id);
        TenantModel::where('id', $tenant->id)->update(['tenant_code' => 'TE' . $tenant_code]);

        $tenant_name = $tenant->first_name . ' ' . $tenant->last_name;

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('tenant','create',$tenant->id,$request->user()->id,$request->user()->pm_company_id,$tenant_name, 'tenant_added');

        // return $this->sendResponse([], 'tenant added successfully.', 200, 200);
        return $this->sendResponse([], __(app()->getLocale().'.tenant_added'), 200, 200);

    }


    //POST
    public function view_tenant_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), ['tenant_id' => 'required|numeric|exists:tenants,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $payment = [];
        $contract = [];

        $currency_id = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->value('currency_id');
        $symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

        $tenant_details = TenantModel::where('id', $request->tenant_id)->select(
            'first_name',
            'id as tenant_id',
            'last_name',
            'country_code',
            'phone',
            'email',
            'country_id',
            'tenant_code'
        )->first();

        $tenant_details->country_name = \DB::table('countries')->where('id', $tenant_details->country_id)->value('country');

        $tenant_details->tenant_name = blank($tenant_details->first_name) ? '' : $tenant_details->first_name . ' ' . $tenant_details->last_name;

        $tenant_unit_details = TenantsUnitModel::where('tenant_id', $request->tenant_id)->select(
            'unit_no',
            'owner_id',
            'id as tenant_unit_id',
            'monthly_rent',
            'bathrooms',
            'rooms',
            'unit_code',
            'building_id'
        )
            ->first();

        if (!blank($tenant_unit_details)) {

            $temp = \DB::table('buildings')->where('id', $tenant_unit_details->building_id)->first();
            $tenant_unit_details->building_name = $temp->building_name;
            $tenant_unit_details->location_link = $temp->location_link;
            $tenant_unit_details->symbol = $symbol;
            $tenant_unit_details->owner_name = \DB::table('owners')->where('id', $tenant_unit_details->owner_id)->value('name');


            $contract = \DB::table('contracts_tables')
                ->where('unit_id', $tenant_unit_details->tenant_unit_id)
                ->where('Tenant_id', $request->tenant_id)  // anil
                ->select('name', 'end_date', 'id', 'start_date')
                ->get()
                ->transform(function ($query) {
                    $query->contract_end_date = date('d M Y', strtotime($query->end_date));
                    $query->contract_statrt_date = date('d M Y', strtotime($query->start_date));
                    $query->is_expired = (Carbon::parse($query->end_date) < Carbon::now()) ? 1 : 0;

                    unset($query->end_date);
                    unset($query->start_date);
                    return $query;
                });


            $payment = \DB::table('payments')
                ->where('unit_id', $tenant_unit_details->tenant_unit_id)
                ->where('tenant_id', $request->tenant_id) //anil
                ->select('id', 'amount', 'payment_type', 'payment_date', 'status')
                ->get()
                ->transform(function ($query) use ($symbol) {
                    // $query->payment_type_int = $query->payment_type;
                    $query->payment_amount =  $symbol . $query->amount;
                    $query->payment_date = date('d M Y', strtotime($query->payment_date));
                    $query->payment_type = $query->payment_type == 1 ? 'cheque' : 'manual';

                    $pay_status_int = $query->status;

                    $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment_status');


                    // switch ($pay_status_int) {
                    //     case 1:
                    //         $pay_status_string = 'Upcoming Payment';
                    //         break;
                    //     case 2:
                    //         $pay_status_string = 'Voided';
                    //         break;
                    //     case 3:
                    //         $pay_status_string = 'Settled';
                    //         break;
                    //     case 4:
                    //         $pay_status_string = 'Overdue';
                    //         break;
                    //     case 5:
                    //         $pay_status_string = 'Cheque Returned';
                    //         break;
                    //     case 6:
                    //         $pay_status_string = 'Voided';
                    //         break;
                    //     case 7:
                    //         $pay_status_string = 'Upcoming Payment';
                    //         break;
                    //     case 8:
                    //         $pay_status_string = 'Overdue';
                    //         break;
                    //     case 9:
                    //         $pay_status_string = 'Paid';
                    //         break;
                    //     case 10:
                    //         $pay_status_string = 'Payment In Default';
                    //         break;
                    // }

                    $query->payment_status = $pay_status_string;
                    return $query;
                });
        } else {
            $tenant_unit_details = (object)[];
        }

        $response = [
            'success' => true,
            'tenant_details'    => $tenant_details,
            'tenant_unit_details' => $tenant_unit_details,
            'contract' => $contract,
            'payment' => $payment,
            'message' => 'view_tenant_by_tenant_id',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    public function country_code_dropdown(Request $request)
    {
        $countries = Country::select('country_code')->get();
        return $this->sendResponse($countries, 'country_code_dropdown', 200, 200);
    }


    // POST
    // tenant table status
    // 0 - Pending Approval   req
    // 1 - Approved
    // 2- Declined    req
    // 3- Disconnected
     public function delete_tenant_request(Request $request){
        $validator = validator($request->all(), ['tenant_id' => 'required|numeric|exists:tenants,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $req = TenantModel::where('id', $request->tenant_id)->first();
        if($req->status == 0){

            $deleted_tenant_name = \DB::table('tenants')
            ->where('id', $request->tenant_id)
            ->select('first_name','last_name')
            ->first();

            $tenant_name = $deleted_tenant_name->first_name.$deleted_tenant_name->last_name;

            //module,action,affected_record_id,pm_id,pm_company_id,record_name
            \App\Services\PmLogService::pm_log_delete_entry('tenant','delete',$request->tenant_id,$request->user()->id,$request->user()->pm_company_id,$tenant_name,'tenant_deleted');

            TenantModel::where('id', $request->tenant_id)->delete();
            // return $this->sendResponse( [] ,'Tenant request deleted successfully',200,200);
            return $this->sendResponse( [] ,__(app()->getLocale().'.tenant_request_deleted'),200,200);

        }else{
            // return $this->sendSingleFieldError('Sorry, only pending approval requests can be deleted', 201, 200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.only_pending_approval_requests_deleted'), 201, 200);

        }
    }

    // POST
    public function update_tenant(Request $request)
    {
        $validator = validator($request->all(), PmRequest::update_tenant());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        // \Log::notice('update_tenant'.json_encode($request->all()));
       $temp_data =  TenantModel::where('id', $request->tenant_id)->select('first_name','last_name')->first();

       $tenant_name = $temp_data->first_name.$temp_data->last_name;

        TenantModel::where('id', $request->tenant_id)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
        ]);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('tenant','edit',$request->tenant_id,$request->user()->id,$request->user()->pm_company_id,$tenant_name, 'tenant_edit');

        // return $this->sendResponse([], 'Tenant updated successfully.', 200, 200);
        return $this->sendResponse([], __(app()->getLocale().'.tenant_updated'), 200, 200);

    }


    //type
    //1 all
    //linked 2
    // 3 unlinked
    //4 request
    // tenant table status --------------
    // 0 - Pending Approval   req
    // 1 - Approved
    // 2- Declined    req
    // 3- Disconnected
    public function search_all_tenants(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'type' => 'required',
            'page' => 'required',
            // 'filter_by_status' => 'required|in:0,1,2,3,5',//5 all
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        // all tenant
        if($request->type == 1) {

            $validator = validator($request->all(), [
                'filter_by_status' => 'required|in:1,3,5',//5 all
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            $tenants_details_search_result_query = TenantModel::leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
                ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                ->where(function ($query) use ($search_key) {
                    $query->where('tenants_units.unit_no', 'LIKE', "%$search_key%")
                        ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.email', 'LIKE', "%$search_key%");
                });

                if($request->filter_by_status != 5){
                    $tenants_details_search_result_query->where('tenants.status', $request->filter_by_status);
                }else{
                    //@secondphase
                    $tenants_details_search_result_query->whereIn('tenants.status', [1,3]);
                }

                $tenants_details_search_result = $tenants_details_search_result_query->select(
                    'tenants.id',
                    'tenants.first_name',
                    'tenants.last_name',
                    'tenants.country_code',
                    'tenants.phone',
                    'tenants.status',
                    'tenants.email',
                    'buildings.building_name',
                    'tenants_units.unit_no',
                    'tenants_units.id as tenant_unit_id',
                    'buildings.id as buildings_id',
                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($query) use ($search_key) {

                   $_temp_tenant = \DB::table('tenants')->where('id', $query->id)
                   ->select('first_name', 'last_name','status')->first();

                   $query->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;
                   $status_int = blank($_temp_tenant) ? 0 : $_temp_tenant->status;

                    switch ($status_int) {
                    //@secondphase
                    //    case 0:
                    //        $status = 'Pending Approval';
                    //        break;
                       case 1:
                           $status = 'Approved';
                           break;
                    //    case 2:
                    //        $status = 'Declined';
                    //        break;
                       case 3:
                           $status = 'Disconnected';
                           break;
                   }

                   $query->status = $status;
                   return $query;
                });

            $tenants_details_search_result_count_query = TenantModel::leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
                ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                ->where(function ($query) use ($search_key) {
                    $query->where('tenants_units.unit_no', 'LIKE', "%$search_key%")
                        ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.email', 'LIKE', "%$search_key%");
                });

                if($request->filter_by_status != 5){
                    $tenants_details_search_result_count_query->where('tenants.status', $request->filter_by_status);
                }else{
                    //@secondphase
                    $tenants_details_search_result_count_query->whereIn('tenants.status', [1,3]);
                }

                $tenants_details_search_result_count = $tenants_details_search_result_count_query->count();
        }

        //linked
        if($request->type == 2){

            $tenants_details_search_result = TenantModel::join('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.status', 1)
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {

                $query->where('tenants_units.unit_no', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.email', 'LIKE', "%$search_key%");

            })
            ->select(
                'tenants.id',
                'tenants.first_name',
                'tenants.last_name',
                'tenants.status',
                'tenants.country_code',
                'tenants.phone',
                'tenants.email',
                'buildings.building_name',
                'tenants_units.unit_no',
                'tenants_units.id as tenant_unit_id',
                'buildings.id as buildings_id',
            )
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function ($query) use ($search_key) {

                $_temp_tenant = \DB::table('tenants')->where('id', $query->id)
                ->select('first_name', 'last_name')->first();
               $query->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;
               $status_int = $query->status;

               switch ($status_int) {
                   case 1:
                       $status = 'Approved';
                       break;
               }

               $query->status = $status;

               return $query;

            });

            $tenants_details_search_result_count = TenantModel::join('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.status', 1)
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {

                $query->where('tenants_units.unit_no', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.email', 'LIKE', "%$search_key%");
            })
            ->count();

        }

        //unlinked
        if($request->type == 3){

            $tenants_details_search_result =  \DB::table('tenants')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'country_code', 'status')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            //@secondphase
            // ->whereIn('tenants.status', [0, 2, 3])
            ->whereIn('tenants.status', [1,3])
            ->whereNotIn('id', function ($query) use ($request) {
                //2 get all tenant id of linked tenants
                $query->select('tenants.id')
                    ->from('tenants')
                    ->join('tenants_units', 'tenants.id', 'tenants_units.tenant_id')
                    ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                     //@secondphase
                     // ->whereIn('tenants.status', [0, 2, 3])
                     ->whereIn('tenants.status', [1,3])
                    ->get();
            })
            ->where(function ($query) use ($search_key) {

                $query->where('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.email', 'LIKE', "%$search_key%");

            })
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function ($query) {
                $_temp_tenant = \DB::table('tenants')->where('id', $query->id)
                ->select('first_name', 'last_name')->first();
               $query->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;

               $status_int = $query->status;

                switch ($status_int) {
                    //@secondphase
                    // case 0:
                    //     $status = 'Pending Approval';
                    //     break;
                    case 1:
                        $status = 'Approved';
                        break;
                    // case 2:
                    //     $status = 'Declined';
                    //     break;
                    case 3:
                        $status = 'Disconnected';
                        break;
                }

                $query->status = $status;
                $query->building_name = '';
                $query->unit_no = '';
                $query->tenant_unit_id = '';
                $query->buildings_id = '';

                return $query;
            });

            $tenants_details_search_result_count = \DB::table('tenants')
            ->select('id')
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            //@secondphase
            // ->whereIn('tenants.status', [0, 2, 3])
            ->whereIn('tenants.status', [1,3])
            ->whereNotIn('id', function ($query) use ($request) {
                //2 get all tenant id of linked tenants
                $query->select('tenants.id')
                    ->from('tenants')
                    ->join('tenants_units', 'tenants.id', 'tenants_units.tenant_id')
                    ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                    //@secondphase
                    // ->whereIn('tenants.status', [0, 2, 3])
                    ->whereIn('tenants.status', [1,3])
                    ->get();
            })
            ->where(function ($query) use ($search_key) {

                $query->where('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.email', 'LIKE', "%$search_key%");

            })
            ->count();
        }

        if($request->type == 4){

            $tenants_details_search_result = TenantModel::join('tenants_units', 'tenants.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants.building_id', '=', 'buildings.id')
            ->where('tenants.status', 0)
            ->where('tenants.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {

                $query->where('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.email', 'LIKE', "%$search_key%")
                    ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");

            })
            ->select(
                'tenants.id',
                'tenants.status',
                'tenants.first_name',
                'tenants.last_name',
                'tenants.country_code',
                'tenants.phone',
                'tenants.email',
                'buildings.building_name',
                'tenants_units.unit_no',
                'tenants_units.id as tenant_unit_id',
                'buildings.id as buildings_id',
            )
            ->take(10)
            ->skip($skip)
            ->get()

            ->transform(function ($query) {
                // $query->requested_date = date('d M Y', strtotime($query->created_at));
                $query->tenant_name = $query->first_name . ' ' . $query->last_name;
                $query->status = $query->status == 0 ? 'Pending Approval' : '';

                return $query;
            });

            $tenants_details_search_result_count = TenantModel::join('tenants_units', 'tenants.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'tenants.building_id', '=', 'buildings.id')
                ->where('tenants.status', 0)
                ->where('tenants.pm_company_id', $request->user()->pm_company_id)
                ->where(function ($query) use ($search_key) {

                    $query->where('tenants.first_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.phone', 'LIKE', "%$search_key%")
                        ->orWhere('tenants.email', 'LIKE', "%$search_key%")
                        ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                        ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");

                })
                ->count();
        }

        $response = [
            'success' => true,
            'data'    => $tenants_details_search_result,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($tenants_details_search_result_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }
}
