<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContractModel;
use App\Models\ContractFilesModel;
use App\Models\PaymentModel;
use App\Http\Controllers\Api\ApiBaseController;
use Illuminate\Support\Carbon;
use App\Http\Requests\PmRequest;
use App\Helpers\Helper;




class PropertyManagerThreeController extends ApiBaseController
{

    // POST
    //PM
    public function contract_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',// 0 expired, 1 active, 2 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $contract_list_query =  ContractModel::join('buildings', 'contracts_tables.building_id', '=', 'buildings.id')
        ->leftjoin('tenants', 'contracts_tables.Tenant_id', '=', 'tenants.id')
        ->join('tenants_units', 'contracts_tables.unit_id', '=', 'tenants_units.id')
        ->where('contracts_tables.pm_company_id', $request->user()->pm_company_id);

        if($request->filter_by_status != 2){
            $contract_list_query->where('contracts_tables.status', $request->filter_by_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $contract_list_query->whereDate('contracts_tables.start_date', '>=', $startDate);

            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $contract_list_query->whereDate('contracts_tables.end_date', '<=', $endDate);
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $contract_list_query->whereDate('contracts_tables.start_date', '=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $contract_list_query->whereDate('contracts_tables.end_date', '=', $endDate);
        }

        $contract_list = $contract_list_query->select(
            'contracts_tables.id',
            'contracts_tables.name',
            'contracts_tables.Tenant_id',
            'contracts_tables.start_date',
            'contracts_tables.end_date',
            'buildings.building_name',
            'tenants_units.unit_no',
            'contracts_tables.status'
        )
        ->take(10)->skip($skip)->get()

        ->transform(function ($query) {
            $_temp_tenant = \DB::table('tenants')->where('id', $query->Tenant_id)
                ->select('first_name', 'last_name')->first();
            $query->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;
            $query->start_date = date('d M Y', strtotime($query->start_date));
            $query->end_date = date('d M Y', strtotime($query->end_date));
            $query->is_expired = (Carbon::parse($query->end_date) < Carbon::now()) ? 1 : 0;

            //change status in db if required.
            if (($query->is_expired == 1) && ($query->status == 1)) {
                ContractModel::where('id', $query->id)->update(['status' => 0]);
                $query->status = 0;
            }
            return $query;
        });



        $contract_list_count_query = ContractModel::join('buildings', 'contracts_tables.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'contracts_tables.unit_id', '=', 'tenants_units.id')
            ->where('contracts_tables.pm_company_id', $request->user()->pm_company_id);

        if($request->filter_by_status != 2){
            $contract_list_count_query->where('contracts_tables.status', $request->filter_by_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $contract_list_count_query->whereDate('contracts_tables.start_date', '>=', $startDate);

            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $contract_list_count_query->whereDate('contracts_tables.end_date', '<=', $endDate);
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $contract_list_count_query->whereDate('contracts_tables.start_date', '=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $contract_list_count_query->whereDate('contracts_tables.end_date', '=', $endDate);
        }

        $contract_list_count = $contract_list_count_query->count();


        $response = [
            'success' => true,
            'data'    => $contract_list,
            'message' => 'contract_list_by_company_id',
            'pagecount'  => (int)ceil($contract_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // POST
    public function view_contract_by_contract_id(Request $request)
    {
        $validator = validator($request->all(), ['contract_id' => 'required|numeric|exists:contracts_tables,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $contract_details = ContractModel::where('id', $request->contract_id)
            ->select(
                'name',
                'start_date',
                'end_date',
                'created_at',
                'status',
                'id',
                'building_id',
                'unit_id',
                'Tenant_id'
            )
            ->first();

        $contract_details->status = $contract_details->status == 1 ? 'Active' : 'Deactive';
        $contract_details->start_date = date('d M Y', strtotime($contract_details->start_date));
        $contract_details->end_date = date('d M Y', strtotime($contract_details->end_date));
        $contract_details->start_date_to_fill = date('Y/d/m', strtotime($contract_details->start_date));
        $contract_details->end_date_to_fill = date('Y/d/m', strtotime($contract_details->end_date));
        $contract_details->created_on = date('d M Y', strtotime($contract_details->created_at));
        $contract_details->all_files = ContractFilesModel::where('contract_id', $contract_details->id)->select('file_name')->get();
        $contract_details->building_name = \DB::table('buildings')->where('id', $contract_details->building_id)->value('building_name');
        $temp = \DB::table('tenants_units')->where('id', $contract_details->unit_id)->select('unit_no', 'unit_code')->first();
        $contract_details->unit_no = $temp->unit_no;
        $contract_details->unit_code = $temp->unit_code;
        $_temp_tenant = \DB::table('tenants')->where('id', $contract_details->Tenant_id)->select('first_name', 'last_name')->first();
        $contract_details->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;
        unset($contract_details->created_at);

        return $this->sendResponse($contract_details, 'view_contract_by_contract_id', 200, 200);
    }

    // POST
    public function delete_contract(Request $request)
    {
        $validator = validator($request->all(), ['contract_id' => 'required|numeric|exists:contracts_tables,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $deleted_contract_name = \DB::table('contracts_tables')
        ->where('id', $request->contract_id)
        ->value('name');

       //module,action,affected_record_id,pm_id,pm_company_id,record_name
       \App\Services\PmLogService::pm_log_delete_entry('contract','delete',$request->contract_id,$request->user()->id,$request->user()->pm_company_id,$deleted_contract_name,'contract_deleted');


       ContractModel::where('id', $request->contract_id)->delete();

        $file_name = \DB::table('contracts_files_tables')->where('contract_id', $request->contract_id)->select('file_name')->get();

        // dd($file_name);
        foreach ($file_name as $single_file) {

            \App\Services\FileUploadService::delete_contract_document_from_azure($single_file->file_name, 'delete_file_of_contracts_tables');
        }
        ContractFilesModel::where('contract_id', $request->contract_id)->delete();

        // return $this->sendResponse([], 'Contract deleted successfully', 200, 200);
        return $this->sendResponse([],__(app()->getLocale().'.contract_deleted'), 200, 200);

    }


    public function add_payment(Request $request)
    {
        if ($request->tenant_id != 0) {
            $tenant_check = \DB::table('tenants')->where('id', $request->tenant_id)->select('id')->first();
            if (blank($tenant_check)) {
                return $this->sendSingleFieldError('tenant_id is invalid', 201, 200);
            }
        }

        $attributeNames = array(
            'amount' => __(app()->getLocale().'.amount'),
         );
        $validator = validator($request->all(), PmRequest::add_payment());
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        // 1 cheque.
        $j = 0;
        foreach ($request->payment_type as $value) {

            if ($request->payment_type[$j] == 1) {

                $attributeNames = array(
                    'cheque_no' => __(app()->getLocale().'.cheque_no'),
                );
                $validator = validator($request->all(), PmRequest::add_cheque_payment());
                $validator->setAttributeNames($attributeNames);
                if ($validator->fails()) {
                    return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
                }
            } else {
                $validator = validator($request->all(), PmRequest::add_manual_payment());
                if ($validator->fails()) {
                    return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
                }
            }
            $j++;
        } //validations ends.


        $i = 0;
        foreach ($request->amount as $value) {

            if ($request->payment_type[$i] == 1) {

                if($request->has('remark') && count($request->remark) > 0 && !blank($request->remark[$i])){
                    $remark = $request->remark[$i];
                }else{
                    $remark = '';
                }

                $payment = \App\models\PaymentModel::create([
                    'amount' => $request->amount[$i],
                    'pm_company_id' => $request->user()->pm_company_id,
                    'tenant_id' => $request->tenant_id,
                    'building_id' => $request->building_id,
                    'unit_id' => $request->tenant_unit_id,
                    'payment_type' => $request->payment_type[$i],
                    'payment_date' => $request->payment_date[$i],
                    'payment_code' => '',
                    'remark' => $remark,
                    'cheque_no' => $request->cheque_no[$i],
                    'status' => $request->status[$i]
                ]);
            }
            if ($request->payment_type[$i] == 0) {

                if($request->has('remark') && count($request->remark) > 0 && !blank($request->remark[$i])){
                    $remark = $request->remark[$i];
                }else{
                    $remark = '';
                }

                $payment = \App\models\PaymentModel::create([
                    'amount' => $request->amount[$i],
                    'pm_company_id' => $request->user()->pm_company_id,
                    'tenant_id' => $request->tenant_id,
                    'building_id' => $request->building_id,
                    'unit_id' => $request->tenant_unit_id,
                    'payment_type' => $request->payment_type[$i],
                    'payment_date' => $request->payment_date[$i],
                    'payment_code' => '',
                    'cheque_no' => '',
                    'remark' => $remark,
                    'status' => $request->status[$i]
                ]);
            }
            $payment_code =  Helper::generate_uniq_code($payment->id);
            PaymentModel::where('id', $payment->id)->update(['payment_code' => 'PA' . $payment_code]);

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('payment','create',$payment->id,$request->user()->id,$request->user()->pm_company_id,$request->amount[$i], 'payment_added');
            $i++;
        }
        // return $this->sendResponse([], 'payment added successfully.', 200, 200);
        return $this->sendResponse([],__(app()->getLocale().'.payment_added'), 200, 200);
    }


    //PM
    //POST
    //'status',
    /* 1.upcoming payment 2.voided 3.settled  4.Overdue payment 5.cheque returned 6.payment in default*/
    public function payment_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'payment_type' => 'required|in:0,1,2',//manual ->0 , cheque ->1, 2 all
            'payment_status' => 'required|in:0,1,2,3,4,5,6',//status 0 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $search_query = PaymentModel::join('buildings', 'payments.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
            ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id');

        $search_query->where('payments.pm_company_id', $request->user()->pm_company_id);

        if($request->payment_type != 2){
            $search_query->where('payments.payment_type', $request->payment_type);
        }

        if($request->payment_status != 0){
            $search_query->where('payments.status', $request->payment_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_query->whereDate('payments.payment_date', '=', $endDate);
            }else{
                $search_query->whereBetween('payments.payment_date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_query->whereDate('payments.payment_date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_query->whereDate('payments.payment_date', '<=', $endDate);
        }

        $search_query->select(
            'payments.id',
            'payments.amount',
            'buildings.id as buildings_id',
            'tenants_units.id as unit_id',
            'payments.payment_date',
            'payments.payment_type',
            'buildings.building_name',
            'tenants_units.unit_no',
            'payments.tenant_id',
            'payments.status',
            'tenants.last_name',
            'tenants.first_name',
            'payments.pm_company_id',
        )
        ->take(10)
        ->skip($skip);

        $payment_details = $search_query->get()
            ->transform(function ($query) {
                $pay_status_int = $query->status;

                $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

                $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');

                $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                $query->amount =  $currency_symbol.$query->amount;

                $query->status = $pay_status_string;

                $query->status_int = $query->status;

                $query->payment_type = $query->payment_type == 1 ? 'cheque' : 'manual';

                $query->tenant_name = blank($query->first_name) ? '' : $query->first_name . ' ' . $query->last_name;

                $query->payment_date = date('d M Y', strtotime($query->payment_date));

                return $query;
        });

        // count of pages
        $search_query_count = PaymentModel::join('buildings', 'payments.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
            ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id');

        $search_query_count->where('payments.pm_company_id', $request->user()->pm_company_id);

        if($request->payment_type != 2){
            $search_query_count->where('payments.payment_type', $request->payment_type);
        }

        if($request->payment_status != 0){
            $search_query_count->where('payments.status', $request->payment_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_query_count->whereDate('payments.payment_date', '=', $endDate);
            }else{
                $search_query_count->whereBetween('payments.payment_date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_query_count->whereDate('payments.payment_date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_query_count->whereDate('payments.payment_date', '<=', $endDate);
        }

        $payment_count = $search_query_count->count();

        $response = [
            'success' => true,
            'data'    => $payment_details,
            'message' => 'payment_list_by_company_id',
            'pagecount'  => (int)ceil($payment_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // POST
    public function delete_payment(Request $request)
    {
        $validator = validator($request->all(), ['payment_id' => 'required|numeric|exists:payments,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $deleted_payment_amount = \DB::table('payments')
                ->where('id', $request->payment_id)
                ->value('amount');

        //module,action,affected_record_id,pm_id,pm_company_id,record_name
        \App\Services\PmLogService::pm_log_delete_entry('payment','delete',$request->payment_id,$request->user()->id,$request->user()->pm_company_id,$deleted_payment_amount,'payment_deleted');

        PaymentModel::where('id', $request->payment_id)->delete();

        // return $this->sendResponse([], 'payment deleted successfully', 200, 200);
        return $this->sendResponse([],__(app()->getLocale().'.payment_deleted'), 200, 200);

    }

    //POST
    public function view_payment_by_payment_id(Request $request)
    {
        $validator = validator($request->all(), ['payment_id' => 'required|numeric|exists:payments,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $payment_details = PaymentModel::where('id', $request->payment_id)
            ->select(
                'id',
                'cheque_no',
                'amount',
                'tenant_id',
                'building_id',
                'unit_id',
                'payment_date',
                'payment_type',
                'remark',
                'status',
                'payment_code',
                'pm_company_id'
            )
            ->first();

        $pay_status_int = $payment_details->status;

        $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

        $currency_id = \DB::table('property_manager_companies')->where('id', $payment_details->pm_company_id)->value('currency_id');

        $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

        $payment_details->amount = $payment_details->amount;
        $payment_details->symbol = $currency_symbol;

        $payment_details->status_int = $payment_details->status;

        $payment_details->status = $pay_status_string;
        $payment_details->payment_type_int = $payment_details->payment_type;
        $payment_details->payment_date_edit = date('Y/m/d', strtotime($payment_details->payment_date));

        $payment_details->payment_type = $payment_details->payment_type == 1 ? 'cheque' : 'manual';
        $payment_details->payment_date = date('d M Y', strtotime($payment_details->payment_date));

        // get data from tenants.
        $_temp_tenant = \DB::table('tenants')->where('id', $payment_details->tenant_id)->select('first_name', 'last_name')->first();
        $payment_details->tenant_name = blank($_temp_tenant) ? '' : $_temp_tenant->first_name . ' ' . $_temp_tenant->last_name;

        // get data from building tables.
        $_temp_building = \DB::table('buildings')->where('id', $payment_details->building_id)->select('id as buildings_id', 'building_name')->first();
        $payment_details->building_name = blank($_temp_building) ? '' : $_temp_building->building_name;
        $payment_details->building_id = blank($_temp_building) ? '' : $_temp_building->buildings_id;

        // get data from tenants_unit table.
        $_temp_unit = \DB::table('tenants_units')->where('id', $payment_details->unit_id)->select('id as unit_id', 'unit_no')->first();
        $payment_details->unit_no = blank($_temp_unit) ? '' : $_temp_unit->unit_no;
        $payment_details->unit_id = blank($_temp_unit) ? '' : $_temp_unit->unit_id;

        $response = [
            'success' => true,
            'data'    => $payment_details,
            'message' => 'payment_list_by_payment_id',
            'status'  => 200
        ];
        // \Log::info(json_encode($payment_details));
        return response()->json($response, 200);
    }


    // POST
    //PM
    /* manual ->0 , cheque ->1*/
    //'status',
    /* 1.upcoming cheque 2.voided 3.settled  4.Overdue 5.cheque returned
    6.payment in default*/
    public function search_payment_list(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'payment_type' => 'required|in:0,1,2',//manual ->0 , cheque ->1, 2 all
            'payment_status' => 'required|in:0,1,2,3,4,5,6',//status 0 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        $search_query = PaymentModel::join('buildings', 'payments.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
            ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id');

        $search_query->where('payments.pm_company_id', $request->user()->pm_company_id);

        $search_query->where(function ($query) use ($search_key) {
            $query->where('payments.amount', 'LIKE', "%$search_key%")
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants.last_name', 'LIKE', "%$search_key%");
        });

        if($request->payment_type != 2){
            $search_query->where('payments.payment_type', $request->payment_type);
        }

        if($request->payment_status != 0){
            $search_query->where('payments.status', $request->payment_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_query->whereDate('payments.payment_date', '=', $endDate);
            }else{
                $search_query->whereBetween('payments.payment_date', [$startDate, $endDate]);
            }
        }
        elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_query->whereDate('payments.payment_date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_query->whereDate('payments.payment_date', '<=', $endDate);
        }

        $search_query->select(
            'payments.id',
            'payments.amount',
            'buildings.id as buildings_id',
            'tenants_units.id as unit_id',
            'payments.payment_date',
            'payments.payment_type',
            'buildings.building_name',
            'tenants_units.unit_no',
            'payments.tenant_id',
            'payments.status',
            'tenants.last_name',
            'tenants.first_name'
        )
            ->take(10)
            ->skip($skip);

        $payment_list = $search_query->get()
            ->transform(function ($query) {
                $pay_status_int = $query->status;

            $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment status');

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
                $query->status = $pay_status_string;

                $query->status_int = $query->status;

                $query->payment_type = $query->payment_type == 1 ? 'cheque' : 'manual';

                $query->tenant_name = blank($query->first_name) ? '' : $query->first_name . ' ' . $query->last_name;

                $query->payment_date = date('d M Y', strtotime($query->payment_date));

                return $query;
        });


        // count of pages
        $search_query_count = PaymentModel::join('buildings', 'payments.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')
            ->leftjoin('tenants', 'payments.tenant_id', '=', 'tenants.id');

        $search_query_count->where('payments.pm_company_id', $request->user()->pm_company_id);

        $search_query_count->where(function ($query) use ($search_key) {
            $query->where('payments.amount', 'LIKE', "%$search_key%")
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants.last_name', 'LIKE', "%$search_key%");
        });

        if($request->payment_type != 2){
            $search_query_count->where('payments.payment_type', $request->payment_type);
        }

        if($request->payment_status != 0){
            $search_query_count->where('payments.status', $request->payment_status);
        }

        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_query_count->whereDate('payments.payment_date', '=', $endDate);
            }else{
                $search_query_count->whereBetween('payments.payment_date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_query_count->whereDate('payments.payment_date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_query_count->whereDate('payments.payment_date', '<=', $endDate);
        }

        $payment_list_count = $search_query_count->count();


        $response = [
            'success' => true,
            'data'    =>  $payment_list,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($payment_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    public function update_payment(Request $request)
    {

        $attributeNames = array(
            'amount' => __(app()->getLocale().'.amount'),
         );

        $validator = validator($request->all(), PmRequest::update_payment());
        $validator->setAttributeNames($attributeNames);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        // 1 cheque.
        if ($request->payment_type == 1) {
            $attributeNames = array(
                'cheque_no' => __(app()->getLocale().'.cheque_no'),
             );
            $validator = validator($request->all(), PmRequest::edit_cheque_payment());
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        } else {
            $validator = validator($request->all(), PmRequest::edit_manual_payment());
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $old_amount = \App\models\PaymentModel::where('id', $request->payment_id)->value('amount');

        if ($request->payment_type == 1) {
            $remark = blank( $request->remark ) ? '' : $request->remark;

            $payment = \App\models\PaymentModel::where('id', $request->payment_id)
                ->update([
                    'amount' => $request->amount,
                    'tenant_id' => $request->tenant_id,
                    'building_id' => $request->building_id,
                    'unit_id' => $request->tenant_unit_id,
                    'payment_type' => $request->payment_type,
                    'payment_date' => $request->payment_date,
                    'remark' => $remark,
                    'cheque_no' => $request->cheque_no,
                    'status' => $request->status

                ]);
        }
        if ($request->payment_type == 0) {
            $remark = blank( $request->remark ) ? '' : $request->remark;

            $payment = \App\models\PaymentModel::where('id', $request->payment_id)
                ->update([
                    'amount' => $request->amount,
                    'tenant_id' => $request->tenant_id,
                    'building_id' => $request->building_id,
                    'unit_id' => $request->tenant_unit_id,
                    'payment_type' => $request->payment_type,
                    'payment_date' => $request->payment_date,
                    'cheque_no' => '',
                    'remark' => $remark,
                    'status' => $request->status
                ]);
        }

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('payment','edit',$request->payment_id,$request->user()->id,$request->user()->pm_company_id,$old_amount, 'payment_edit');

        // return $this->sendResponse([], 'payment updated successfully.', 200, 200);
        return $this->sendResponse( [] ,__(app()->getLocale().'.payment_updated'),200,200);

    }
}
