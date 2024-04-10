<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\MobileRequest;
use App\Models\CommentMediaModel;
use App\Models\MaintanceRequestForModel;
use App\Models\MaintanceRequestModel;
use App\Models\MaintenanceFilesModel;
use App\Models\PaymentModel;
use Illuminate\Http\Request;
use Mail;

class ATenantTwoController extends ApiBaseController
{
    //POST
    public function upcoming_payment_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $upcoming_payment_details = PaymentModel::where('payments.tenant_id', $request->user()->id)
            ->where('payments.unit_id', $request->unit_id)
            ->whereIn('payments.status', [1])
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
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->payment_date = date('d M Y', strtotime($row->payment_date));

                $row->payment = $row->payment_type;

                $pay_status_int = $row->status;

                $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

                // switch ($pay_status_int) {
                //     case 1:
                //         $pay_status_string = 'Upcoming Payment';
                //         break;
                //     case 7:
                //         $pay_status_string = 'Upcoming Payment';
                //         break;
                // }

                $row->payment_status = $pay_status_string;

                $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                //    dd($currency_symbol);

                $row->amount = $currency_symbol . $row->amount;

                $row->payment_method = $row->payment_type == 1 ? 'Cheque' : 'Manual';

                return $row;
            });

        $response = [
            'success' => true,
            'data' => $upcoming_payment_details,
            'message' => 'upcoming_payment_by_tenant_id',
            // 'pagecount'  => (int)ceil($upcoming_payment_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function overdue_payment_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            // 'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // $page = $request->page;
        // $skip = $page ? 10 * ($page - 1) : 0;

        $overdue_payment_details = PaymentModel::where('payments.tenant_id', $request->user()->id)
            ->where('payments.unit_id', $request->unit_id)
            ->whereIn('payments.status', [4, 5, 6])
        // ->whereIn('payments.status', [1, 7])
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
                'tenants_units.unit_no'
            )
        // ->take(10)
        // ->skip($skip)
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->payment_date = date('d M Y', strtotime($row->payment_date));
                $row->payment = $row->payment_type;

                $pay_status_int = $row->status;

                $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

                // switch ($pay_status_int) {

                //     case 4:
                //         $pay_status_string = 'Overdue';
                //         break;
                //     case 5:
                //         $pay_status_string = 'Cheque Returned';
                //         break;

                //     case 8:
                //         $pay_status_string = 'Overdue';
                //         break;

                //     case 10:
                //         $pay_status_string = 'Payment In Default';
                //         break;
                // }

                $row->payment_status = $pay_status_string;

                $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                //    dd($currency_symbol);

                $row->amount = $currency_symbol . $row->amount;

                $row->payment_method = $row->payment_type == 1 ? 'Cheque' : 'Manual';

                return $row;
            });

        // $overdue_payment_details_count = PaymentModel::where('payments.tenant_id', $request->user()->id)
        //     ->whereIn('payments.status', [4, 8, 5, 10])
        //     ->where('payments.unit_id', $request->unit_id)
        //     // ->whereIn('payments.status', [1, 7])
        //     ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
        //     ->join('buildings', 'payments.building_id', '=', 'buildings.id')
        //     ->count();

        $response = [
            'success' => true,
            'data' => $overdue_payment_details,
            'message' => 'overdue_payment_by_tenant_id',
            // 'pagecount'  => (int)ceil($overdue_payment_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function history_payment_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            // 'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // $page = $request->page;
        // $skip = $page ? 10 * ($page - 1) : 0;

        $history_payment_details = PaymentModel::where('payments.tenant_id', $request->user()->id)
            ->where('payments.unit_id', $request->unit_id)
        // ->whereIn('payments.status', [2, 3, 9, 6])
            ->whereIn('payments.status', [2, 3])
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
                'tenants_units.unit_no'
            )
        // ->take(10)
        // ->skip($skip)
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->payment_date = date('d M Y', strtotime($row->payment_date));
                $row->payment = $row->payment_type;

                $pay_status_int = $row->status;

                $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

                // switch ($pay_status_int) {

                //     case 2:
                //         $pay_status_string = 'Voided';
                //         break;
                //     case 3:
                //         $pay_status_string = 'Settled';
                //         break;

                //     case 6:
                //         $pay_status_string = 'Voided';
                //         break;

                //     case 9:
                //         $pay_status_string = 'Paid';
                //         break;

                // }

                $row->payment_status = $pay_status_string;

                $currency_id = \DB::table('property_manager_companies')->where('id', $row->pm_company_id)->value('currency_id');

                $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                //    dd($currency_symbol);

                $row->amount = $currency_symbol . $row->amount;

                $row->payment_method = $row->payment_type == 1 ? 'Cheque' : 'Manual';

                return $row;
            });

        // $history_payment_details_count = PaymentModel::where('payments.tenant_id', $request->user()->id)
        //     ->whereIn('payments.status', [2, 3, 9, 6])
        //     ->where('payments.unit_id', $request->unit_id)
        //     ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
        //     ->join('buildings', 'payments.building_id', '=', 'buildings.id')
        //     ->count();

        $response = [
            'success' => true,
            'data' => $history_payment_details,
            'message' => 'history_payment_details',
            // 'pagecount'  => (int)ceil($history_payment_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    // GET
    public function maintance_request_for(Request $request)
    {
        $maintancerequest = MaintanceRequestForModel::select('maitinance_request_name', 'id')
            ->orderBy('maitinance_request_name', 'asc')
            ->get();
        return $this->sendResponse($maintancerequest, 'maitinance_request_dropdown', 200, 200);
    }

    // POST
    public function add_maintenance_request_for_tenant(Request $request)
    {
        // \Log::notice($request->all());

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception$e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), MobileRequest::add_maintenance_request_for_tenant());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $building_id = \DB::table('tenants_units')->where('id', $request->unit_id)
            ->value('building_id');

        // $preferred_date_time = date("Y-m-d H:i:s", strtotime($request->preferred_date_time));
        // dd($request->preferred_date_time);

        $maintance_request = MaintanceRequestModel::create([
            'tenant_id' => $request->user()->id,
            'building_id' => $building_id,
            'pm_company_id' => $request->user()->pm_company_id,
            'property_manager_id' => 0,
            'unit_id' => $request->unit_id,
            'status' => 1,
            'description' => $request->description,
            'maintenance_request_id' => $request->maintenance_request_for_id,
            'preferred_date_time' => $request->preferred_date_time,
            'request_code' => '',
        ]);

        $request_code = Helper::generate_uniq_code_for_request_code($maintance_request->id);
        MaintanceRequestModel::where('id', $maintance_request->id)->update(['request_code' => $request_code]);

        $temp_data = \DB::table('tenants')->where('id', $request->user()->id)
            ->select('first_name', 'last_name')->first();

        $unit_no = \DB::table('tenants_units')->where('id', $request->unit_id)
            ->value('unit_no');

        $building_name = \DB::table('buildings')->where('id', $building_id)
            ->value('building_name');

        $request_details = \DB::table('maitinance_request_for')->where('id', $request->maintenance_request_for_id)->value('maitinance_request_name');

        $pm_compnay_details = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->select('email', 'name')->first();

        $pm_compnay_name = $pm_compnay_details->name;

        $pm_compnay_email = $pm_compnay_details->email;

        $tenant_name = $temp_data->first_name . '' . $temp_data->last_name;

        $inputs = [];
        $inputs['tenant_name'] = $tenant_name;
        $inputs['buildings_name'] = $building_name;
        $inputs['unit_no'] = $unit_no;
        $inputs['request_for'] = $request_details;
        $inputs['pm_company_name'] = $pm_compnay_name;
        $inputs['request_code'] = $request_code;
        $inputs['description'] = $request->description;

        try {
            Mail::to($pm_compnay_email)->send(new \App\Mail\TenantGenrateNewMaintenanceReq($inputs));
            // Mail::to('sfs.anil21@gmail.com')->send(new \App\Mail\TenantGenrateNewMaintenanceReq($inputs));

        } catch (\Exception$e) {
            \Log::error('---------add_maintance_request------------------- ' . $e);
        }
        // Mail::to($request->user()->pm_company_id)->send(new \App\Mail\SendOtp($code));

        // return $this->sendResponse($maintance_request, 'Request created successfully, Please upload Attachments.', 200, 200);
        return $this->sendResponse($maintance_request, __(app()->getLocale() . '.upload_attachments'), 200, 200);
    }

    //•       status should be as below
    //-        Request raised (1)
    //-        Request assigned (2)
    //-        Request completed (3)
    //-        Request is on hold (4)
    //-        Request canceled (5)
    //POST
    public function current_request_list_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            // 'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $current_request_details = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
            ->where('maintance_requests.unit_id', $request->unit_id)
            ->whereIn('maintance_requests.status', [1, 2, 4]) // 1 ==  request raised.
            ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
            ->select(
                'maintance_requests.id',
                'maintance_requests.pm_company_id',
                'maintance_requests.preferred_date_time',
                'maintance_requests.request_code',
                'maintance_requests.status',
                'maintance_requests.created_at',
                'buildings.building_name',
                'tenants_units.unit_no',
                'maitinance_request_for.maitinance_request_name'
            )
        // ->take(10)
        // ->skip($skip)
            ->orderBy('maintance_requests.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->date = date('d M Y', strtotime($row->created_at));

                $maintance_requests_status_int = $row->status;

                switch ($maintance_requests_status_int) {

                    case 1:
                        $maintance_requests_status_string = 'Request Raised';
                        break;
                    case 2:
                        $maintance_requests_status_string = 'Request Assigned';
                        break;
                    case 4:
                        $maintance_requests_status_string = 'Request is on Hold';
                        break;
                }

                $row->maintance_requests_status = $maintance_requests_status_string;

                return $row;
            });

        // $current_request_details_count = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
        //     ->where('maintance_requests.unit_id', $request->unit_id)
        //     ->whereIn('maintance_requests.status', [1, 2, 4]) //1 == request raised.
        //     ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
        //     ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
        //     ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
        //     ->count();

        $response = [
            'success' => true,
            'data' => $current_request_details,
            'message' => 'current_request_list_by_tenant_id',
            // 'pagecount'  => (int)ceil($current_request_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function close_request_list_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            // 'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // $page = $request->page;
        // $skip = $page ? 10 * ($page - 1) : 0;

        $close_request_details = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
            ->where('maintance_requests.unit_id', $request->unit_id)
            ->where('maintance_requests.status', 3) // 3  ==  request closed.
            ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
            ->select(
                'maintance_requests.id',
                'maintance_requests.pm_company_id',
                'maintance_requests.preferred_date_time',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',
                'buildings.building_name',
                'tenants_units.unit_no',
                'maitinance_request_for.maitinance_request_name'
            )
        // ->take(10)
        // ->skip($skip)
            ->orderBy('maintance_requests.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->date = date('d M Y', strtotime($row->created_at));

                $maintance_requests_status_int = $row->status;

                switch ($maintance_requests_status_int) {

                    case 3:
                        $maintance_requests_status_string = 'Request Closed';
                        break;
                }

                $row->maintance_requests_status = $maintance_requests_status_string;

                return $row;
            });

        // $close_request_details_count = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
        //     ->where('maintance_requests.status', 3) // 3 == request closed.
        //     ->where('maintance_requests.unit_id', $request->unit_id)
        //     ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
        //     ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
        //     ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
        //     ->count();

        $response = [
            'success' => true,
            'data' => $close_request_details,
            'message' => 'close_request_list_by_tenant_id',
            // 'pagecount'  => (int)ceil($close_request_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function cancel_request_list_by_tenant_id(Request $request)
    {
        $validator = validator($request->all(), [
            // 'page' => 'required|numeric',
            'unit_id' => 'required|numeric|exists:tenants_units,id',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $cancel_request_details = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
            ->where('maintance_requests.unit_id', $request->unit_id)
            ->where('maintance_requests.status', 5) // 5  ==  request cancel.
            ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
            ->select(
                'maintance_requests.id',
                'maintance_requests.pm_company_id',
                'maintance_requests.preferred_date_time',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',
                'buildings.building_name',
                'tenants_units.unit_no',
                'maitinance_request_for.maitinance_request_name'
            )
        // ->take(10)
        // ->skip($skip)
            ->orderBy('maintance_requests.id', 'DESC')
            ->get()
            ->transform(function ($row) {
                $row->date = date('d M Y', strtotime($row->created_at));

                $maintance_requests_status_int = $row->status;

                switch ($maintance_requests_status_int) {

                    case 5:
                        $maintance_requests_status_string = 'Request Cancel';
                        break;
                }

                $row->maintance_requests_status = $maintance_requests_status_string;

                return $row;
            });

        // $cancel_request_details_count = MaintanceRequestModel::where('maintance_requests.tenant_id', $request->user()->id)
        //     ->where('maintance_requests.status', 5) // 5 == request cancel.
        //     ->where('maintance_requests.unit_id', $request->unit_id)
        //     ->join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
        //     ->join('buildings', 'maintance_requests.building_id', '=', 'buildings.id')
        //     ->join('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
        //     ->count();

        $response = [
            'success' => true,
            'data' => $cancel_request_details,
            'message' => 'cancel_request_list_by_tenant_id',
            // 'pagecount'  => (int)ceil($cancel_request_details_count / 10),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function cancel_request_by_maintenance_request_id(Request $request)
    {

        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception$e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        MaintanceRequestModel::where('maintance_requests.id', $request->maintenance_request_id)
            ->update(['status' => 5]);

        $response = [
            'success' => true,
            'data' => [],
            'message' => __(app()->getLocale() . '.request_cancelled'),
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function view_maintanence_request_by_maintenance_request_id(Request $request)
    {
        $validator = validator($request->all(), ['maintenance_request_id' => 'required|numeric|exists:maintance_requests,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $maintenance_details = MaintanceRequestModel::where('id', $request->maintenance_request_id)
            ->select('id', 'request_code', 'maintenance_request_id', 'building_id', 'unit_id', 'description', 'tenant_id', 'status', 'description', 'created_at', 'preferred_date_time')
            ->first();

        // get data from bulidings.
        $_temp_data = \DB::table('tenants_units')->where('id', $maintenance_details->unit_id)->value('building_id');

        $building_name = \DB::table('buildings')->where('id', $_temp_data)->value('building_name');

        $maintenance_details->building_name = $building_name;

        // get data from maintenance
        $_temp_data = \DB::table('maitinance_request_for')->where('id', $maintenance_details->maintenance_request_id)->select('maitinance_request_name', 'id')->first();

        $maintenance_details->request_for = $_temp_data->maitinance_request_name;

        $maintenance_details->date = date('d M Y', strtotime($maintenance_details->created_at));

        // get data from tenant_unit
        $maintenance_details->unit_no = \DB::table('tenants_units')->where('id', $maintenance_details->unit_id)->value('unit_no');

        // if ($maintenance_details->tenant_id != 0) {
        //     //get tenant details
        //     $tenant_details = \DB::table('tenants')->where('id', $maintenance_details->tenant_id)->select('last_name', 'first_name')->first();

        //     $maintenance_details->requested_by = !blank($tenant_details) ? $tenant_details->first_name . ' ' . $tenant_details->last_name : '';
        // } else {
        //     $maintenance_details->requested_by = '';
        // }

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

        $maintenance_details->status_int = $maintenance_details->status;

        $maintenance_details->maintance_requests_status = $status_string;

        // unset($item->created_at);
        // unset($item->maintenance_request_id);
        unset($maintenance_details->unit_id);
        unset($maintenance_details->building_id);
        unset($maintenance_details->tenant_id);
        unset($maintenance_details->created_at);
        unset($maintenance_details->maintenance_request_id);
        // unset($item->maitinance_request_name);

        // get expert details
        $experts = \DB::table('experts')
            ->join('maintenance_experts', 'maintenance_experts.expert_id', '=', 'experts.id')
            ->where('maintenance_experts.maintenance_id', $request->maintenance_request_id)
            ->select('experts.name', 'experts.phone', 'maintenance_experts.visit_date_time', 'maintenance_experts.id', 'experts.id as expert_id', 'experts.country_code')
            ->get()
            ->transform(function ($item) {

                $item->visitinge_date = date('d-M-y (h:i A)', strtotime($item->visit_date_time));

                unset($item->visit_date_time);

                $specialisties_experts = \DB::table('specialisties_expert_id')
                    ->where('expert_id', $item->expert_id)
                    ->select('speciality_id')
                    ->get();

                $item->all_specialities = '';

                foreach ($specialisties_experts as $_value) {
                    $_name = \DB::table('specialities')
                        ->where('id', $_value->speciality_id)
                        ->value('name');

                    //    \Log::info(json_encode($_name));

                    if (!blank($_name)) {
                        $item->all_specialities .= $_name;
                        $item->all_specialities .= ',';
                    }
                }

                return $item;
            });

        if (blank($experts)) {

            $experts = [];
        }

        // $MaintenanceFiles = MaintenanceFilesModel::where('maintenance_request_id', $request->maintenance_request_id)
        //     ->select('file_name', 'file_type', 'thumbnail_name', 'id')
        //     ->get();

        $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
            ->select('file_name', 'file_type', 'thumbnail_name', 'id')
            ->get();

        if (blank($MaintenanceFiles)) {

            $MaintenanceFiles = [];
        }

        $response = [
            'success' => true,
            'data' => $maintenance_details,
            'MaintenanceFiles' => $MaintenanceFiles,
            'experts' => $experts,
            'message' => 'view_maintanence_request_by_request_id',
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    //POST
    public function upload_maintenance_files_by_maintenance_request_id(Request $request)
    {


        try {
            \App::setLocale($_SERVER['HTTP_LANG']);
        } catch (\Exception$e) {
            return $this->sendSingleFieldError('Sorry, language is required in header', 201, 200);
        }

        $validator = validator($request->all(), MobileRequest::upload_maintenance_files_by_maintenance_request_id());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //upload image.
        if ($request->file_type == 1) {

            try {

                $maintenace_files = MaintenanceFilesModel::create([
                    'maintenance_request_id' => $request->maintenance_request_id,
                    'file_type' => $request->file_type,
                    'file_name' => $request->attachment,
                    'thumbnail_name' => $request->thumbnail_name,
                    'upload_by' => $request->user()->id,
                ]);
            } catch (\Exception$e) {
                \Log::error('---------upload_maintenance_files image---------- ' . $e);
            }

            $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
                ->select('file_name', 'file_type', 'thumbnail_name', 'id')
                ->get();

        }
        //upload video.
        if ($request->file_type == 2) {

            $maintenace_files = MaintenanceFilesModel::create([
                'maintenance_request_id' => $request->maintenance_request_id,
                'file_type' => $request->file_type,
                'file_name' => $request->attachment,
                'thumbnail_name' => $request->thumbnail_name,
                'upload_by' => $request->user()->id,
            ]);

            $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
                ->select('file_name', 'file_type', 'thumbnail_name', 'id')
                ->get();
        }
        return $this->sendResponse($MaintenanceFiles, __(app()->getLocale() . '.file_upload_successfully'), 200, 200);
    }

    //POST
    public function fetch_maintanence_request_all_attachment_by_maintenance_request_id(Request $request)
    {
        $validator = validator($request->all(), [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //get data from MaintenanceFilesModel
        $MaintenanceFiles = MaintenanceFilesModel::where('maintenance_request_id', $request->maintenance_request_id)
            ->select('file_name', 'file_type', 'thumbnail_name', 'id')
            ->get();

        // $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
        //     ->select('file_name', 'file_type', 'thumbnail_name', 'id')
        //     ->get();

        // get data from CommentMediaModel
        $CommentMedia = CommentMediaModel::where('maintenance_request_id', $request->maintenance_request_id)
            ->select('media_name', 'thumbnail_name', 'id', 'media_type')
            ->get();

        if (blank($MaintenanceFiles)) {
            $MaintenanceFiles = [];
        }

        if (blank($CommentMedia)) {
            $CommentMedia = [];
        }

        $response = [
            'success' => true,
            'MaintenanceFiles' => $MaintenanceFiles,
            'CommentMedia' => $CommentMedia,
            'message' => 'fetch_maintanence_request_all_attachment',
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
