<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintanceRequestForModel;
use App\Http\Controllers\Api\ApiBaseController;
use App\Models\ExpertModel;
use App\Models\MaintanceExpertModel;
use App\Models\MaintanceRequestModel;
use App\Http\Requests\PmRequest;
use App\Helpers\Helper;
use Mail;



class MaintanceRequestController extends ApiBaseController
{
    // GET
    // GET
    public function maintance_request(Request $request)
    {
        $lang = app()->getLocale();

        if($lang == 'en'){

            $maintancerequest = MaintanceRequestForModel::select('maitinance_request_name', 'id')
            ->orderBy('maitinance_request_name', 'asc')
            ->get();
        }
        elseif($lang == 'ar'){

            $maintancerequest = MaintanceRequestForModel::select('arabic_maintenance_request_name as maitinance_request_name', 'id')
            ->orderBy('arabic_maintenance_request_name', 'asc')
            ->get();
        }
        return $this->sendResponse($maintancerequest, 'maitinance_request_dropdown', 200, 200);
    }

    // GET
    public function expert_dropdown(Request $request)
    {
        $expert_dropdown = ExpertModel::where('pm_company_id', $request->user()->pm_company_id)
            ->select('name', 'id', 'phone')
            ->orderBy('name', 'asc')
            ->get();
        return $this->sendResponse($expert_dropdown, 'expert_dropdown', 200, 200);
    }


    // POST
    public function add_maintanence_request(Request $request)
    {
        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), PmRequest::add_maintanence_request());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $validator = validator($request->all(), [
            "expert_id"    => "nullable|array",
            "expert_id.*"  => 'nullable|numeric|exists:experts,id',

            "visit_date_time" => "nullable|array",
            "visit_date_time.*"  => 'nullable',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ($request->tenant_id != 0) {
            $tenant_check = \DB::table('tenants')->where('id', $request->tenant_id)->select('id')->first();
            if (blank($tenant_check)) {
                return $this->sendSingleFieldError('tenant_id is invalid', 201, 200);
            }
        }

        $maintance_request = MaintanceRequestModel::create([
            'tenant_id' => $request->tenant_id,
            'building_id' => $request->building_id,
            'pm_company_id' => $request->user()->pm_company_id,
            'property_manager_id' => $request->user()->id,
            'unit_id' => $request->unit_id,
            'status' => $request->status,
            'description' => $request->description,
            'maintenance_request_id' => $request->maintenance_request_id,
            'request_code' => '',
        ]);

        $request_code =  Helper::generate_uniq_code_for_request_code($maintance_request->id);
        MaintanceRequestModel::where('id', $maintance_request->id)->update(['request_code' => $request_code]);

        $maintanence_request_details = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
        ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
        ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
        ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

        ->where('maintance_requests.id', $maintance_request->id)
        ->select(
            'maintance_requests.id',
            'maintance_requests.maintenance_request_id',
            'maintance_requests.unit_id',
            'maintance_requests.building_id',
            'maintance_requests.tenant_id',
            'maintance_requests.status',
            'maintance_requests.request_code',
            'maintance_requests.created_at',
            'maintance_requests.description',
            'tenants.first_name',
            'tenants.last_name',
            'tenants.phone',
            'tenants.country_code',
            'buildings.building_name',
            'buildings.address',
            'tenants_units.unit_no',
        )
        ->first();

        $maintanence_request_details->request_for = \DB::table('maitinance_request_for')->where('id', $maintanence_request_details->maintenance_request_id)->value('maitinance_request_name');

        if ($maintanence_request_details->tenant_id != 0) {
            switch ($maintanence_request_details->status) {
                case 1:
                    $status_string = 'Request Raised';
                    break;
                case 2:
                    $status_string = 'Request Assigned';
                    break;
                case 3:
                    $status_string = 'Request completed';
                    break;
                case 4:
                    $status_string = 'Request is on hold';
                    break;
                case 5:
                    $status_string = 'Request canceled';
                    break;
            }
            $maintanence_request_details->status = $status_string;
        }

        foreach ($request->expert_id as $key => $insert) {

            $unique_code =  Helper::generate_uniq_code($request->expert_id[$key]);

            $visit_datetime = date("Y-m-d H:i:s", strtotime($request->visit_date_time[$key]));

             MaintanceExpertModel::create([
                'maintenance_id' => $maintance_request->id,
                'expert_id' => $request->expert_id[$key],
                'unique_code' => $unique_code,
                'visit_date_time' => $visit_datetime,
            ]);

            $expert_details = \DB::table('experts')->where('id', $request->expert_id[$key])->select('phone','name','country_code','email')->first();

            $phone = $expert_details->country_code.$expert_details->phone;

            \App\Services\SendSmsNotificationExpert::smsNotification($phone,$maintanence_request_details,$expert_details->name,$unique_code,$expert_details->email,$visit_datetime);
        }

        $_status = $request->status;

        //Once request is assigned to an expert (expert field is not null) then status will be “request assigned”.
        if (!blank($request->expert_id) && $request->status == 1) {
            MaintanceRequestModel::where('id', $maintance_request->id)->update(['status' => 2]);
            $_status = 2;
        }

        // When PM creates the request – the tenant to receive an email and notification in mobile with the request details.

        if ($request->tenant_id != 0) {
            switch ($_status) {
                case 1:
                    $status_string = 'Request Raised';
                    break;
                case 2:
                    $status_string = 'Request Assigned';
                    break;
                case 3:
                    $status_string = 'Request completed';
                    break;
                case 4:
                    $status_string = 'Request is on hold';
                    break;
                case 5:
                    $status_string = 'Request canceled';
                    break;
            }

            $status = $status_string;

            //get tenant details
            $tenant_details = \DB::table('tenants')->where('id', $request->tenant_id)->select('email', 'first_name')->first();

            //get buildings details
            $buildings_details = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');

            //get unit details
            $unit_details = \DB::table('tenants_units')->where('id', $request->unit_id)->value('unit_no');

            //get maitinance_request_name
            $request_details = \DB::table('maitinance_request_for')->where('id', $request->maintenance_request_id)->value('maitinance_request_name');

            //get pm_details
            $pm_details = \DB::table('property_managers')->where('id', $request->user()->id)->value('name');

            //get pm_company_details
            $pm_company_details = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->value('name');

            //get expert details
            $expert_details =  \DB::table('experts')->whereIn('id', $request->expert_id)->select('name', 'phone')->get();

            $inputs = [];
            $inputs['tenant_name'] = $tenant_details->first_name;
            $inputs['buildings_name'] = $buildings_details;
            $inputs['unit_no'] = $unit_details;
            $inputs['request_for'] = $request_details;
            $inputs['status'] =  $status;
            $inputs['expert'] =  $expert_details;
            $inputs['pm_name'] =  $pm_details;
            $inputs['pm_company_name'] =  $pm_company_details;

            try {
                Mail::to($tenant_details->email)->send(new \App\Mail\TenantGetRequestDetails($inputs));
                // Mail::to('sfs.anil21@gmail.com')->send(new \App\Mail\TenantGetRequestDetails($inputs));

            } catch (\Exception $e) {
                \Log::error('---------add_maintance_request------------------- ' . $e);
            }
        }

        $request_for = \DB::table('maitinance_request_for')->where('id', $maintanence_request_details->maintenance_request_id)->value('maitinance_request_name');

        //module,action,affected_record_id,pm_id,pm_company_id
       \App\Services\PmLogService::pm_log_entry('main req','create',$maintanence_request_details->id,$request->user()->id,$request->user()->pm_company_id,$request_for, 'main_req_added');


    //     return $this->sendResponse(['maintenance_request_id' => $maintance_request->id, 'unit_id' =>$request->unit_id
    // ], 'Request created successfully, Please upload Attachments.', 200, 200);

    return $this->sendResponse(['maintenance_request_id' => $maintance_request->id, 'unit_id' =>$request->unit_id
        ], __(app()->getLocale().'.request_created'), 200, 200);
    }



    // post
    public function update_maintanence_request_by_request_id(Request $request)
    {
        $validator = validator($request->all(), PmRequest::update_maintanence());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $validator = validator($request->all(), [
            "expert_id"    => "nullable|array",
            "expert_id.*"  => 'nullable|numeric|exists:experts,id',

            "visit_date_time" => "nullable|array",
            "visit_date_time.*"  => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ($request->tenant_id != 0) {
            $tenant_check = \DB::table('tenants')->where('id', $request->tenant_id)->select('id')->first();
            if (blank($tenant_check)) {
                return $this->sendSingleFieldError('tenant_id is invalid', 201, 200);
            }
        }

        $maintenance_request_id = MaintanceRequestModel::where('id', $request->request_id)->value('maintenance_request_id');

        $old_request_for = \DB::table('maitinance_request_for')->where('id', $maintenance_request_id)->value('maitinance_request_name');

        $old_status = MaintanceRequestModel::where('id', $request->request_id)->value('status');

        MaintanceRequestModel::where('id', $request->request_id)->update([
            'tenant_id' => $request->tenant_id,
            'building_id' => $request->building_id,
            'unit_id' => $request->unit_id,
            'status' => $request->status,
            'description' => $request->description,
            'maintenance_request_id' => $request->maintenance_request_id,
        ]);

          $maintanence_request_details = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
          ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
          ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
          ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

          ->where('maintance_requests.id', $request->request_id)
          ->select(
              'maintance_requests.id',
              'maintance_requests.maintenance_request_id',
              'maintance_requests.unit_id',
              'maintance_requests.building_id',
              'maintance_requests.tenant_id',
              'maintance_requests.status',
              'maintance_requests.request_code',
              'maintance_requests.created_at',
              'maintance_requests.description',
              'tenants.first_name',
              'tenants.last_name',
              'tenants.phone',
              'tenants.country_code',
              'buildings.building_name',
              'buildings.address',
              'tenants_units.unit_no',
          )
          ->first();


          $maintanence_request_details->request_for = \DB::table('maitinance_request_for')->where('id', $maintanence_request_details->maintenance_request_id)->value('maitinance_request_name');

          if ($maintanence_request_details->tenant_id != 0) {
              switch ($maintanence_request_details->status) {
                  case 1:
                      $status_string = 'Request Raised';
                      break;
                  case 2:
                      $status_string = 'Request Assigned';
                      break;
                  case 3:
                      $status_string = 'Request completed';
                      break;
                  case 4:
                      $status_string = 'Request is on hold';
                      break;
                  case 5:
                      $status_string = 'Request canceled';
                      break;
              }
              $maintanence_request_details->status = $status_string;
          }

        foreach ($request->expert_id as $key => $insert) {

            $unique_code =  Helper::generate_uniq_code($request->expert_id[$key]);

            $visit_datetime = date("Y-m-d H:i:s", strtotime($request->visit_date_time[$key]));

            MaintanceExpertModel::create([
                'maintenance_id' => $request->request_id,
                'expert_id' => $request->expert_id[$key],
                'unique_code' => $unique_code,
                'visit_date_time' => $visit_datetime,
            ]);

            $expert_details = \DB::table('experts')->where('id', $request->expert_id[$key])->select('phone','name','country_code','email')->first();
            $phone = $expert_details->country_code.$expert_details->phone;
            \App\Services\SendSmsNotificationExpert::smsNotification($phone,$maintanence_request_details,$expert_details->name,$unique_code,$expert_details->email,$visit_datetime);
        }

        $_status = $request->status;

        // When PM change status –
        //the tenant to receive an email and notification in mobile with the request details.
        if ($old_status != $request->status) {

            if ($request->tenant_id != 0) {
                switch ($_status) {
                    case 1:
                        $status_string = 'Request Raised';
                        break;
                    case 2:
                        $status_string = 'Request Assigned';
                        break;
                    case 3:
                        $status_string = 'Request completed';
                        break;
                    case 4:
                        $status_string = 'Request is on hold';
                        break;
                    case 5:
                        $status_string = 'Request canceled';
                        break;
                }

                $status = $status_string;

                //get tenant details
                $tenant_details = \DB::table('tenants')->where('id', $request->tenant_id)->select('email', 'first_name')->first();

                //get buildings details
                $buildings_details = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');

                //get unit details
                $unit_details = \DB::table('tenants_units')->where('id', $request->unit_id)->value('unit_no');

                //get maitinance_request_name
                $request_details = \DB::table('maitinance_request_for')->where('id', $request->maintenance_request_id)->value('maitinance_request_name');

                //get pm_details
                $pm_details = \DB::table('property_managers')->where('id', $request->user()->id)->value('name');

                //get pm_company_details
                $pm_company_details = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->value('name');


                //get expert details
                $expert_details =  \DB::table('experts')->whereIn('id', $request->expert_id)->select('name', 'phone')->get();

                // dd($expert_details);
                $inputs = [];
                $inputs['tenant_name'] = $tenant_details->first_name;
                $inputs['buildings_name'] = $buildings_details;
                $inputs['unit_no'] = $unit_details;
                $inputs['request_for'] = $request_details;
                $inputs['status'] =  $status;
                $inputs['expert'] =  $expert_details;
                $inputs['pm_name'] =  $pm_details;
                $inputs['pm_company_name'] =  $pm_company_details;

                try {
                    Mail::to($tenant_details->email)->send(new \App\Mail\TenantGetUpdatedRequestDetails($inputs));
                    // Mail::to('sfs.anil21@gmail.com')->send(new \App\Mail\TenantGetUpdatedRequestDetails($inputs));

                } catch (\Exception $e) {
                    \Log::error('---------update_maintanence_request_by_request_id------------------- ' . $e);
                }
            }
        }

        //module,action,affected_record_id,pm_id,pm_company_id.
        \App\Services\PmLogService::pm_log_entry('main req','edit',$request->request_id,$request->user()->id,$request->user()->pm_company_id,$old_request_for, 'main_req_edit');

        // return $this->sendResponse([], 'Request updated successfully, please update attachments if you want', 200, 200);
        return $this->sendResponse([], __(app()->getLocale().'.request_updated'), 200, 200);
    }



    // POST
    public function view_maintanence_request_by_request_id(Request $request)
    {
        $pm_can_edit = 1;

        $validator = validator($request->all(), ['request_id' => 'required|numeric|exists:maintance_requests,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $maintenance_details = MaintanceRequestModel::where('id', $request->request_id)
            ->select('request_code', 'maintenance_request_id', 'id', 'building_id', 'unit_id', 'description', 'tenant_id', 'status' , 'property_manager_id','preferred_date_time')
            ->first();

        if(!blank($maintenance_details->preferred_date_time)){

                $maintenance_details->preferred_date_time = date("d-M-Y H:i", strtotime($maintenance_details->preferred_date_time));

         }else{

               $maintenance_details->preferred_date_time = '';

         }

         // get data from bulidings.
         $building_id = \DB::table('tenants_units')->where('id', $maintenance_details->unit_id)->value('building_id');
         $_temp_data = \DB::table('buildings')->where('id', $building_id)->select('building_name', 'address')->first();

         $maintenance_details->building_name = !blank($_temp_data) ? $_temp_data->building_name : '';

         //building_address
        $maintenance_details->building_address = !blank($_temp_data) ? $_temp_data->address : '';

        //get data from maintenance
        $_temp_data = \DB::table('maitinance_request_for')->where('id', $maintenance_details->maintenance_request_id)->select('maitinance_request_name', 'id')->first();

        $maintenance_details->request_for = $_temp_data->maitinance_request_name;

        $maintenance_details->request_for_id = $_temp_data->id;

        $maintenance_details->pm_can_edit = 1;

        if($maintenance_details->property_manager_id == 0){

            $maintenance_details->pm_can_edit = 0;

        }

        //get data from tenant_unit
        $maintenance_details->unit_no = \DB::table('tenants_units')->where('id', $maintenance_details->unit_id)->value('unit_no');

        if ($maintenance_details->tenant_id != 0) {
            //get tenant details
            $tenant_details = \DB::table('tenants')->where('id', $maintenance_details->tenant_id)->select('last_name', 'first_name')->first();

            $maintenance_details->requested_by = !blank($tenant_details) ? $tenant_details->first_name . ' ' . $tenant_details->last_name : '';
        } else {
            $maintenance_details->requested_by = '';
        }

        switch ($maintenance_details->status) {
            case 1:
                $status_string = ' Request Raised';
                break;
            case 2:
                $status_string = 'Request Assigned';
                break;
            case 3:
                $status_string = 'Request completed';
                break;
            case 4:
                $status_string = 'Request is on hold';
                break;
            case 5:
                $status_string = 'Request canceled';
                break;
        }

        $maintenance_details->status_int =  $maintenance_details->status;

        $maintenance_details->status =  $status_string;


        //get expert details
        $experts = \DB::table('experts')
            ->join('maintenance_experts', 'maintenance_experts.expert_id', '=', 'experts.id')
            ->where('maintenance_experts.maintenance_id', $request->request_id)
            ->select('experts.name', 'experts.phone', 'experts.country_code',  'maintenance_experts.visit_date_time', 'maintenance_experts.id')
            ->get()
            ->transform(function ($item) {

                // $item->request_date = $item->visit_date_time;
                $item->request_date = date("d-M-Y H:i", strtotime($item->visit_date_time));

                $item->phone = '+'.$item->country_code.' '.$item->phone;

                unset($item->visit_date_time);
                return $item;
            });

        $response = [
            'success' => true,
            'data'    => $maintenance_details,
            'experts'    => $experts,
            'message' => 'view_maintanence_request_by_request_id',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



    //POST
    public function unassign_expert_from_maintenance_request(Request $request)
    {
        $validator = validator($request->all(), ['request_expert_id' => 'required|numeric|exists:maintenance_experts,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expert_unassign_from_maintenece_request = \DB::table('maintenance_experts')->where('maintenance_experts.id', $request->request_expert_id)
        ->join('experts','maintenance_experts.expert_id','experts.id')
        ->value('name');

         //module,action,affected_record_id,pm_id,pm_company_id,record_name
         \App\Services\PmLogService::pm_log_delete_entry('expert_unassign','delete',$request->request_expert_id,$request->user()->id,$request->user()->pm_company_id,$expert_unassign_from_maintenece_request,'expert_unassign');

        \DB::table('maintenance_experts')->where('id', $request->request_expert_id)->delete();

        // return $this->sendResponse([], 'Expert unassigned successfully', 200, 200);
        return $this->sendResponse([], __(app()->getLocale().'.expert_unassigned'), 200, 200);

    }


    //post
    //PM
    // status should be as below
    //-	Request raised (1)
    //- Request assigned (2)
    //-	Request completed (3)
    //-	Request is on hold (4)
    //-	Request canceled (5)
    public function search_maintanence_request(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2,3,4,5',// 0 all
            'maintenance_request_for_id' => 'required|numeric',// 0 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ( ($request->maintenance_request_for_id != 0) ) {
            $validator = validator($request->all(), [
                'maintenance_request_for_id' => 'exists:maitinance_request_for,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        \DB::statement("SET SQL_MODE=''");

        $search_expert_maintanence_request = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
            ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
            ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
            ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

            ->where('maintance_requests.pm_company_id', $request->user()->pm_company_id);

            if($request->filter_by_status != 0){
                $search_expert_maintanence_request->where('maintance_requests.status', $request->filter_by_status);
            }

            if($request->maintenance_request_for_id != 0){
                $search_expert_maintanence_request->where('maitinance_request_for.id', $request->maintenance_request_for_id);
            }

            if(!blank($request->date_from) && !blank($request->date_to)){
                $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
                $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
                if($startDate == $endDate){
                    $search_expert_maintanence_request->whereDate('maintance_requests.created_at', '=', $endDate);
                }else{
                    $search_expert_maintanence_request->whereBetween('maintance_requests.created_at', [$startDate, $endDate]);
                }
            }elseif(!blank($request->date_from)){
                $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
                $search_expert_maintanence_request->whereDate('maintance_requests.created_at', '>=', $startDate);
            }elseif(!blank($request->date_to)){
                $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
                $search_expert_maintanence_request->whereDate('maintance_requests.created_at', '<=', $endDate);
            }

            $search_expert_maintanence_request->where(function ($query) use ($search_key) {

                $query->where('experts.name', 'LIKE', "%$search_key%")
                    ->orWhere('maintance_requests.request_code', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                    ->orWhere('maitinance_request_for.maitinance_request_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");
              });

            $search_expert_maintanence_request->select(
                'maintance_requests.id',
                'maintance_requests.maintenance_request_id',
                'maintance_requests.unit_id',
                'maintance_requests.building_id',
                'maintance_requests.tenant_id',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',
                'experts.name',
                'tenants.first_name',
                'tenants.last_name',
                'maitinance_request_for.maitinance_request_name',
                'buildings.building_name',
                'tenants_units.unit_no',
            )
            ->take(10)
            ->skip($skip);

            $all_data =   $search_expert_maintanence_request
            ->groupBy('maintance_requests.id')
            ->get()
            ->transform(function ($item) {

                $item->request_date = date('d M Y', strtotime($item->created_at));

                switch ($item->status) {
                    case 1:
                        $status_string = 'Request Raised';
                        break;
                    case 2:
                        $status_string = 'Request Assigned';
                        break;
                    case 3:
                        $status_string = 'Request completed';
                        break;
                    case 4:
                        $status_string = 'Request is on hold';
                        break;
                    case 5:
                        $status_string = 'Request canceled';
                        break;
                }
                $item->status = $status_string;

                $item->requested_by = $item->first_name.' '.$item->last_name;

                $item->request_for = $item->maitinance_request_name;

                $item->expert = $item->name;

                unset($item->created_at);
                unset($item->maintenance_request_id);
                unset($item->unit_id);
                unset($item->building_id);
                unset($item->tenant_id);
                unset($item->first_name);
                unset($item->last_name);
                unset($item->name);
                unset($item->maitinance_request_name);

                return $item;
            });


        $search_expert_maintanence_request_count = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
        ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
        ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
        ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
        ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
        ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

        ->where('maintance_requests.pm_company_id', $request->user()->pm_company_id);

        if($request->filter_by_status != 0){
            $search_expert_maintanence_request_count->where('maintance_requests.status', $request->filter_by_status);
        }

        if($request->maintenance_request_for_id != 0){
            $search_expert_maintanence_request_count->where('maitinance_request_for.id', $request->maintenance_request_for_id);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_expert_maintanence_request_count->whereDate('maintance_requests.created_at', '=', $endDate);
            }else{
                $search_expert_maintanence_request_count->whereBetween('maintance_requests.created_at', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_expert_maintanence_request_count->whereDate('maintance_requests.created_at', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_expert_maintanence_request_count->whereDate('maintance_requests.created_at', '<=', $endDate);
        }

        $search_expert_maintanence_request_count->where(function ($query) use ($search_key) {

            $query->where('experts.name', 'LIKE', "%$search_key%")
                ->orWhere('maintance_requests.request_code', 'LIKE', "%$search_key%")
                ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants.last_name', 'LIKE', "%$search_key%")
                ->orWhere('maitinance_request_for.maitinance_request_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");
        });

        $count = $search_expert_maintanence_request_count
        ->distinct('maintance_requests.id')
        ->count();


        $response = [
            'success' => true,
            'data'    => $all_data,
            'message' => 'search_maintanence_request list',
            'pagecount'  => (int)ceil($count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

}
