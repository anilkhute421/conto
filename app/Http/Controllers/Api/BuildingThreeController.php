<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuildingModel;
use App\Models\PaymentModel;
use App\Models\MaintanceRequestModel;
use App\Models\ContactRequestModel;
use App\Models\PropertyManager;
use App\Models\ContractModel;
use App\Models\ContractFilesModel;
use App\Http\Controllers\Api\ApiBaseController;


class BuildingThreeController extends ApiBaseController
{
    //
    public function building_delete_by_building_id(Request $request)
    {
        try {
            $validator = validator($request->all(), ['building_id' => 'required|numeric|exists:buildings,id']);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            $building = \DB::table('tenants_units')
                ->where('building_id', $request->building_id)
                ->value('building_id');

            $building_two = \DB::table('avaliable_units')
                ->where('building_id', $request->building_id)
                ->value('building_id');

            if (blank($building) && blank($building_two)) {

                $deleted_building_name = \DB::table('buildings')
                    ->where('id', $request->building_id)
                    ->value('building_name');

                //module,action,affected_record_id,pm_id,pm_company_id,record_name
                \App\Services\PmLogService::pm_log_delete_entry('building', 'delete', $request->building_id, $request->user()->id, $request->user()->pm_company_id, $deleted_building_name, 'building_deleted');


                BuildingModel::where('id', $request->building_id)->delete();
                // return $this->sendResponse([], 'Building has been deleted successfully.', 200, 200);
                return $this->sendResponse([], __(app()->getLocale() . '.building_deleted'), 200, 200);
            } else {

                // return $this->sendResponse([], 'Sorry building can not be deleted at this moment due to units, units available related to this building found.', 201, 200);
                return $this->sendResponse([], __(app()->getLocale() . '.building_cannot_deleted_due_to_units'), 201, 200);
            }
        } catch (\Throwable $th) {
            // \Log::info($th);
            return $this->sendSingleFieldError('There is some error in this api', 201, 200);
        }
    }


    public function pm_contact_to_admin(Request $request)
    {

        $validator = validator($request->all(), ['description' => 'required|max:255']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $contact = ContactRequestModel::create([
            'description' => $request->description,
            'property_manager_id' => $request->user()->id

        ]);

        //  return $this->sendResponse([] ,'Contact added successfully',200,200);
        return $this->sendResponse([], __(app()->getLocale() . '.contact_added'), 200, 200);
    }

    //POST
    public function payment_list_by_unit_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $check = \DB::table('tenants_units')->where('id', $request->unit_id)->value('tenant_id');

        // dd($check);

        if ($check == 0) {

            $unit_payment_details = PaymentModel::where('payments.tenant_id', 0)
                ->where('payments.unit_id', $request->unit_id)
                ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'payments.building_id', '=', 'buildings.id')
                ->select(
                    'payments.id',
                    'payments.payment_type',
                    'payments.status',
                    'payments.amount',
                    'payments.payment_date',
                    'buildings.building_name',
                    'buildings.pm_company_id',
                    'tenants_units.unit_no',
                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($row) {
                    $row->payment_date = date('d M Y', strtotime($row->payment_date));

                    $row->payment = $row->payment_type;

                    $pay_status_int = $row->status;

                    $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment_status');

                    $row->status = $pay_status_string;

                    $row->tenant_name = '';


                    $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                    $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                    //    dd($currency_symbol);

                    $row->amount =  $currency_symbol . $row->amount;

                    $row->payment_method = $row->payment_type == 1 ? 'cheque' : 'manual';

                    return $row;
                });

            $unit_payment_details_count = PaymentModel::where('payments.tenant_id', 0)
                ->where('payments.unit_id', $request->unit_id)
                ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'payments.building_id', '=', 'buildings.id')
                ->count();
        } else {

            $unit_payment_details = PaymentModel::where('payments.tenant_id', $check)
                ->where('payments.unit_id', $request->unit_id)
                // ->whereIn('payments.status', [1, 7])
                ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'payments.building_id', '=', 'buildings.id')
                ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id')
                ->select(
                    'payments.id',
                    'payments.payment_type',
                    'payments.status',
                    'payments.amount',
                    'payments.payment_date',
                    'buildings.building_name',
                    'buildings.pm_company_id',
                    'tenants_units.unit_no',
                    'tenants.first_name',
                    'tenants.last_name'
                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($row) {
                    $row->payment_date = date('d M Y', strtotime($row->payment_date));

                    // $row->payment = $row->payment_type;

                    $pay_status_int = $row->status;

                    $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment_status');

                    $row->status = $pay_status_string;

                    $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                    $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                    // $temp_data = \DB::table('tenants')->where('id', $check)->select('first_name','last_name')->first();

                    $row->tenant_name = $row->first_name . '' . $row->last_name;

                    $row->amount =  $currency_symbol . $row->amount;

                    $row->payment_type = $row->payment_type == 1 ? 'cheque' : 'manual';

                    return $row;
                });

            $unit_payment_details_count = PaymentModel::where('payments.tenant_id',  $check)
                ->where('payments.unit_id', $request->unit_id)
                ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'payments.building_id', '=', 'buildings.id')
                ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id')
                ->count();
        }

        $response = [
            'success' => true,
            'data'    => $unit_payment_details,
            'message' => 'unit_payment_details',
            'pagecount'  => (int)ceil($unit_payment_details_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    public function maintenance_request_list_by_unit_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;


        $check = \DB::table('tenants_units')->where('id', $request->unit_id)->value('tenant_id');

        // dd($check);

        if ($check == 0) {

            \DB::statement("SET SQL_MODE=''");

            $maintenance_request_details = MaintanceRequestModel::where('maintance_requests.tenant_id', 0)
                ->where('maintance_requests.unit_id', $request->unit_id)
                ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
                ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
                ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
                ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
                // ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

                ->select(
                    'maintance_requests.id',
                    // 'maintance_requests.maintenance_request_id',
                    // 'maintance_requests.unit_id',
                    // 'maintance_requests.building_id',
                    // 'maintance_requests.tenant_id',
                    'maintance_requests.status',
                    'maintance_requests.request_code',
                    'maintance_requests.created_at',
                    'experts.name',
                    // 'tenants.first_name',
                    // 'tenants.last_name',
                    'maitinance_request_for.maitinance_request_name',
                    'buildings.building_name',
                    'tenants_units.unit_no',
                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($row) {
                    $row->date = date('d M Y', strtotime($row->created_at));

                    $maintance_requests_status_int = $row->status;

                    switch ($maintance_requests_status_int) {

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

                    $row->maintance_requests_status = $status_string;

                    $row->requested_by = '';

                    return $row;
                });



            $maintenance_request_details_count = MaintanceRequestModel::where('maintance_requests.tenant_id', 0)
                ->where('maintance_requests.unit_id', $request->unit_id)
                ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
                ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
                ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
                ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
                ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')
                ->distinct('maintance_requests.id')
                ->count();
        } else {

            \DB::statement("SET SQL_MODE=''");

            $maintenance_request_details = MaintanceRequestModel::where('maintance_requests.tenant_id', $check)
                ->where('maintance_requests.unit_id', $request->unit_id)
                ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
                ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
                ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
                ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
                ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
                ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')
                ->select(
                    'maintance_requests.id',
                    // 'maintance_requests.maintenance_request_id',
                    // 'maintance_requests.unit_id',
                    // 'maintance_requests.building_id',
                    // 'maintance_requests.tenant_id',
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
                ->skip($skip)
                ->groupBy('maintance_requests.id')
                ->get()
                ->transform(function ($row) {
                    $row->date = date('d M Y', strtotime($row->created_at));

                    $maintance_requests_status_int = $row->status;

                    switch ($maintance_requests_status_int) {

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

                    $row->maintance_requests_status = $status_string;


                    $row->requested_by = $row->first_name . '' . $row->last_name;

                    return $row;
                });



            $maintenance_request_details_count = MaintanceRequestModel::where('maintance_requests.tenant_id', $check)
                ->where('maintance_requests.unit_id', $request->unit_id)
                ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
                ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
                ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
                ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
                ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')
                ->distinct('maintance_requests.id')
                ->count();
        }

        $response = [
            'success' => true,
            'data'    => $maintenance_request_details,
            'message' => 'current_request_list_by_tenant_id',
            'pagecount'  => (int)ceil($maintenance_request_details_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    public function contract_list_by_unit_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;


        $check = \DB::table('tenants_units')->where('id', $request->unit_id)->value('tenant_id');

        // dd($check);

        if ($check == 0) {

            $contract_details = ContractModel::where('contracts_tables.Tenant_id', 0)
                ->where('contracts_tables.unit_id', $request->unit_id)
                ->select(
                    'id',
                    'start_date',
                    'end_date',
                    'created_at',
                    'name',

                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($row) {

                    $row->created_on = date('d M Y', strtotime($row->created_at));

                    $row->start_date = date('d M Y', strtotime($row->start_date));

                    $row->end_date = date('d M Y', strtotime($row->end_date));

                    $row->all_files = ContractFilesModel::where('contract_id', $row->id)->select('file_name')->get();

                    return $row;
                });

            $contract_details_count = ContractModel::where('contracts_tables.Tenant_id', 0)
                ->where('contracts_tables.unit_id', $request->unit_id)
                ->count();
        } else {


            $contract_details = ContractModel::where('contracts_tables.Tenant_id', $check)
                ->where('contracts_tables.unit_id', $request->unit_id)
                ->select(
                    'id',
                    'start_date',
                    'end_date',
                    'created_at',
                    'name',
                )
                ->take(10)
                ->skip($skip)
                ->get()
                ->transform(function ($row) {

                    $row->created_on = date('d M Y', strtotime($row->created_at));

                    $row->start_date = date('d M Y', strtotime($row->start_date));

                    $row->end_date = date('d M Y', strtotime($row->end_date));

                    $row->all_files = ContractFilesModel::where('contract_id', $row->id)->select('file_name')->get();

                    return $row;
                });

            $contract_details_count = ContractModel::where('contracts_tables.Tenant_id', $check)
                ->where('contracts_tables.unit_id', $request->unit_id)
                ->count();
        }

        $response = [
            'success' => true,
            'data'    => $contract_details,
            'message' => 'contract_list_by_unit_id',
            'pagecount'  => (int)ceil($contract_details_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



    //POST
    public function pm_logout(Request $request)
    {

        $user = PropertyManager::find($request->user()->id);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('profile', 'logout', $request->user()->id, $request->user()->id, $request->user()->pm_company_id, 'logout', 'pm_logout');

        $user->tokens()->delete();

        $response = [
            'success' => true,
            'data'    => [],
            // 'message' => 'Logout successful',
            'message' => __(app()->getLocale() . '.logout'),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //POST
    public function tenant_verify_by_pm(Request $request)
    {

        $validator = validator($request->all(), [
            'tenant_id' => 'required|numeric|exists:tenants,id',
            'accept_decline' => 'required|numeric|in:1,2',  //1 -> accept // 2 -> decline    
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ($request->accept_decline == 1) {

            \DB::table('tenants')->where('id', $request->tenant_id)->update(['status' => 1]);

            return $this->sendResponse([], __(app()->getLocale() . '.tenant_verify'), 200, 200);
        }

        if ($request->accept_decline == 2) {

            \DB::table('tenants')->where('id', $request->tenant_id)->update(['status' => 2]);

            return $this->sendResponse([], __(app()->getLocale() . '.tenant_unverify'), 200, 200);
        }
    }
}
