<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\Api\UserRequests;
use Illuminate\Http\Request;
use App\Models\BuildingModel;
use App\Models\Owner;
use App\Helpers\Helper;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AvaliableUnit;
use App\Models\AvailableUnitImageModel;
use App\Models\CountryCurrencyModel;
use App\Models\TenantsUnitModel;
use App\Models\TenantModel;
use App\Models\PropertyManagerCompany;
use Illuminate\Support\Carbon;
use App\Models\ContractModel;
use App\Models\ContractFilesModel;

class BuildingManageTwoController extends ApiBaseController
{

    // POST
    //PM
    public function search_contract_list(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2', // 0 expired, 1 active, 2 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        $contract_list_query =  ContractModel::join('buildings', 'contracts_tables.building_id', '=', 'buildings.id')
            ->leftjoin('tenants', 'contracts_tables.Tenant_id', '=', 'tenants.id')
            ->join('tenants_units', 'contracts_tables.unit_id', '=', 'tenants_units.id')
            ->where('contracts_tables.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {

                $query->where('contracts_tables.name', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%");
            });

        if ($request->filter_by_status != 2) {
            $contract_list_query->where('contracts_tables.status', $request->filter_by_status);
        }

        if (!blank($request->date_from) && !blank($request->date_to)) {
            $startDate = \Illuminate\Support\Carbon::create($request->date_from);
            $contract_list_query->whereDate('contracts_tables.start_date', '>=', $startDate);

            $endDate = \Illuminate\Support\Carbon::create($request->date_to);
            $contract_list_query->whereDate('contracts_tables.end_date', '<=', $endDate);
        } elseif (!blank($request->date_from)) {
            $startDate = \Illuminate\Support\Carbon::create($request->date_from);
            $contract_list_query->whereDate('contracts_tables.start_date', '=', $startDate);
        } elseif (!blank($request->date_to)) {
            $endDate = \Illuminate\Support\Carbon::create($request->date_to);
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

            ->transform(function ($query) use ($search_key) {
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
            ->where('contracts_tables.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {
                $query->where('contracts_tables.name', 'LIKE', "%$search_key%")
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%");
            });

        if ($request->filter_by_status != 2) {
            $contract_list_count_query->where('contracts_tables.status', $request->filter_by_status);
        }

        if (!blank($request->date_from) && !blank($request->date_to)) {
            $startDate = \Illuminate\Support\Carbon::create($request->date_from);
            $contract_list_count_query->whereDate('contracts_tables.start_date', '>=', $startDate);

            $endDate = \Illuminate\Support\Carbon::create($request->date_to);
            $contract_list_count_query->whereDate('contracts_tables.end_date', '<=', $endDate);
        } elseif (!blank($request->date_from)) {
            $startDate = \Illuminate\Support\Carbon::create($request->date_from);
            $contract_list_count_query->whereDate('contracts_tables.start_date', '=', $startDate);
        } elseif (!blank($request->date_to)) {
            $endDate = \Illuminate\Support\Carbon::create($request->date_to);
            $contract_list_count_query->whereDate('contracts_tables.end_date', '=', $endDate);
        }

        $contract_list_count = $contract_list_count_query->count();


        $response = [
            'success' => true,
            'data'    =>  $contract_list,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($contract_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //linking / unlinking tenant
    //show pop up
    public function on_change_tenant_drop_down_of_tenant_unit_edit(Request $request)
    {
        $validator = validator($request->all(), [
            'tenant_id'  => 'required|numeric|exists:tenants,id',
            'tenant_unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $show_pop_up = 0;
        $msg_to_show = '';
        $case = '';
        $list_to_show = [];  //for payments.
        $record_to_show = []; //for contracts.

        $currency_id = \DB::table('property_manager_companies')->where('id',  $request->user()->pm_company_id)->value('currency_id');
        $symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

        //Case 2.1: Active contract for selected unit exists and the tenant is empty in these contracts,
        //payments records with empty tenants related to selected unit doesn’t exist

        // case 2.1
        // case 2.2
        $contract = \DB::table('contracts_tables')
            ->where('unit_id', $request->tenant_unit_id)
            ->where('end_date', '>', \DB::raw('CURDATE()'))
            ->where('Tenant_id', 0)
            ->select('id', 'name', 'end_date', 'start_date')
            ->get()
            ->transform(function ($item) {
                $item->start_date = date('d M Y', strtotime($item->start_date));
                $item->end_date = date('d M Y', strtotime($item->end_date));
                return $item;
            });

        //case 2.1
        //case 2.2
        //Case 2.2: Active contract for selected unit exists and the tenant is empty in these contracts,
        // payments records exists for the selected unit and the tenant is empty in these payments
        $payments = \DB::table('payments')
            ->where('unit_id', $request->tenant_unit_id)
            ->where('tenant_id', 0)
            ->select('id', 'payment_type', 'status', 'amount', 'payment_date', 'remark', 'payment_code', 'cheque_no')
            ->get()
            ->transform(function ($query) use ($symbol) {

                $pay_status_int = $query->status;

                $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int,'payment_status');

                $query->status = $pay_status_string;

                $query->amount = $symbol . $query->amount;

                $query->payment_type = $query->payment_type == 1 ? 'Cheque' : 'Manual';

                $query->payment_date = date('d M Y', strtotime($query->payment_date));

                return $query;
            });


        // 2.1
        if (!blank($contract) && blank($payments)) {
            $case = '2.1';
            // $msg_to_show = 'Existing Active contracts related to selected unit, please confirm to update tenant details in Active contracts, expired contracts will not get affected';
            $msg_to_show = __(app()->getLocale() . '.existing_active_contracts');
            $record_to_show =  $contract;
            $show_pop_up = 1;
        }

        //2.2
        if (!blank($contract) && !blank($payments)) {
            $case = '2.2';
            // $msg_to_show = 'Existing contracts and payments related to selected unit, please confirm to update tenant details in payments';
            $msg_to_show = __(app()->getLocale() . '.existing_contracts_and_payments');
            $list_to_show = $payments;
            $record_to_show =  $contract;
            $show_pop_up = 1;
        }

        //2.3
        //contract for selected unit does not exist, payments records exists
        $contract_2_3 = \DB::table('contracts_tables')
            ->where('unit_id', $request->tenant_unit_id)
            ->where('end_date', '>', \DB::raw('CURDATE()'))
            ->where('Tenant_id', 0)
            ->select('id', 'name', 'end_date', 'start_date')
            ->get()
            ->transform(function ($item) {
                $item->start_date = date('d M Y', strtotime($item->start_date));
                $item->end_date = date('d M Y', strtotime($item->end_date));
                return $item;
            });


        //2.3
        $payments_2_3 = \DB::table('payments')
            ->where('unit_id', $request->tenant_unit_id)
            ->where('tenant_id', 0)
            ->select('id', 'payment_type', 'status', 'amount', 'payment_date', 'remark', 'payment_code', 'cheque_no')
            ->get()
            ->transform(function ($query) use ($symbol) {

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

                $query->status = $pay_status_string;

                $query->amount = $symbol . $query->amount;

                $query->payment_type = $query->payment_type == 1 ? 'Cheque' : 'Manual';

                $query->payment_date = date('d M Y', strtotime($query->payment_date));

                return $query;
            });

        //2.3
        if (blank($contract_2_3) && !blank($payments_2_3)) {
            $case = '2.3';
            // $msg_to_show = 'Existing payments related to selected unit, please confirm to update tenant details in payments';
            $msg_to_show = __(app()->getLocale() . '.existing_payments_related_to_selected_unit');
            $list_to_show = $payments_2_3;
            $show_pop_up = 1;
        }

        //Case 2.4: contract for selected unit does not exist, payments records does not exist
        $response = [
            'success' => true,
            'case' =>  $case,
            'show_pop_up'    => $show_pop_up,
            'msg_to_show'    =>  $msg_to_show,
            'list_to_show' => $list_to_show,
            'record_to_show' => $record_to_show,
            'message' => 'on_change_tenant_drop_down_of_tenant_unit_edit',
            'status'  => 200
        ];
        return response()->json($response, 200);
    } //on_change_tenant_drop_down_of_tenant_unit_edit


    //  POST (CASE->4.1)
    public function disconnect_confirm_decline_for_tenant_unit(Request $request)
    {
        $validator = validator($request->all(), [
            'tenant_unit_id' => 'required|numeric|exists:tenants_units,id',
            'accept_decline' => 'required|in:1',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $tenant_id = \DB::table('tenants_units')
            ->where('id', $request->tenant_unit_id)
            ->value('tenant_id');

        if ($request->accept_decline == 1) {

            $temp_data = \DB::table('tenants')->where('id', $tenant_id)->select('first_name', 'last_name')->first();

            $old_tenant_name = $temp_data->first_name . $temp_data->last_name;

            \DB::table('tenants_units')
                ->where('id', $request->tenant_unit_id)
                ->where('tenant_id',  $tenant_id)
                ->update(['tenant_id' => 0]);

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('unit', 'edit', $request->tenant_unit_id, $request->user()->id, $request->user()->pm_company_id, $old_tenant_name, 'tenant_unit_edit');


            $found_tenant_connected_to_another_unit = \DB::table('tenants_units')
                ->where('id', '!=', $request->tenant_unit_id)
                ->where('tenant_id',  $tenant_id)
                ->select('id')
                ->first();

            if (blank($found_tenant_connected_to_another_unit)) {

                $temp_data = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name', 'last_name')->first();
                if(!blank($temp_data)){
                $tenant_name = $temp_data->first_name . $temp_data->last_name;
                //module,action,affected_record_id,pm_id,pm_company_id
                \App\Services\PmLogService::pm_log_entry('tenant', 'staus', $tenant_id, $request->user()->id, $request->user()->pm_company_id, $tenant_name, 'tenant_status');
                }
                \DB::table('tenants')
                    ->where('id',  $tenant_id)
                    ->update(['status' => 3]);
            }
        }

        $response = [
            'success' => true,
            // 'message' => 'Disconnect successfully',
            'message' => __(app()->getLocale() . '.disconnected'),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // POST
    // case 4.1
    // show pop up
    public function disconnect_popup_for_tenant_unit(Request $request)
    {
        $validator = validator($request->all(), [
            'tenant_unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //API contracts = select from contracts where unit ID = unit.id and contract.tenant.ID = unit.tenant.id

        $tenant_id = \DB::table('tenants_units')
            ->where('id', $request->tenant_unit_id)
            ->value('tenant_id');

        $contract = \DB::table('contracts_tables')
            ->where('end_date', '>', \DB::raw('CURDATE()'))
            ->where('Tenant_id',  $tenant_id)
            ->where('unit_id', $request->tenant_unit_id)
            ->select('id', 'name', 'end_date', 'start_date')
            ->first();

        if (!blank($contract)) {
            // $msg_to_show = 'Active contracts found for this tenant, please expire or delete the contract and then try to disconnect again';
            $msg_to_show = __(app()->getLocale() . '.expire_or_delete_contract');

            $response = [
                'success' => true,
                'msg_to_show'    =>  $msg_to_show,
                'show_confirm_decline_buttons' => 0,
                'message' => 'disconnect_popup_for_tenant_unit',
                'status'  => 200
            ];
            return response()->json($response, 200);
        }

        // API payments = select active payments (where payment status not in (voided, settled or Payment in Default) where payment tenant = tenant ID,
        /* 1.upcoming cheque 2.voided 3.settled  4.Overdue 5.cheque returned
        6.voided  7.upcoming Payment 8.overdue 9.paid 10. payment in default*/
        $payments = \DB::table('payments')
            ->where('unit_id', $request->tenant_unit_id)
            ->where('tenant_id',  $tenant_id)
            ->whereNotIn('status', [3, 2, 6])
            // ->whereNotIn('status', [3, 2, 6, 10, 9])
            ->select('id', 'payment_type', 'status', 'amount', 'payment_date', 'remark', 'payment_code', 'cheque_no')
            ->get();

        if (!blank($payments)) {
            // $msg_to_show = 'Active payments found for this tenant, please void, delete or change payment status to “payment on default or Settled” and then try to disconnect again';
            $msg_to_show = __(app()->getLocale() . '.active_payments_found_tenant');

            $response = [
                'success' => true,
                'msg_to_show'    =>  $msg_to_show,
                'show_confirm_decline_buttons' => 0,
                'message' => 'disconnect_popup_for_tenant_unit',
                'status'  => 200
            ];
            return response()->json($response, 200);
        }

        // $msg_to_show = 'Are you sure you want to disconnect the tenant?';
        $msg_to_show = __(app()->getLocale() . '.disconnect_the_tenant');

        $response = [
            'success' => true,
            'msg_to_show'    =>  $msg_to_show,
            'show_confirm_decline_buttons' => 1,
            'message' => 'disconnect_popup_for_tenant_unit',
            'status'  => 200
        ];
        return response()->json($response, 200);
    } //disconnect_popup_for_tenant_unit //show pop up


    // POST (CASE->4.2)
    //show pop up
    // public function unlinked_for_tenant_view(Request $request)
    // {
    //     $validator = validator($request->all(), [
    //         'tenant_id' => 'required|numeric|exists:tenants,id',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
    //     }

    //     //API contracts = select from contracts where unit ID = unit.id and contract.tenant.ID = unit.tenant.id

    //     $msg_to_show = '';

    //     $contract = \DB::table('contracts_tables')
    //         ->where('end_date', '>', \DB::raw('CURDATE()'))
    //         ->where('Tenant_id',  $request->tenant_id)
    //         ->select('id', 'name', 'end_date', 'start_date')
    //         ->first();

    //     if (!blank($contract)) {
    //         $msg_to_show = 'Active contracts found for this tenant, please expire or delete the contract and then try to disconnect again';
    //         $response = [
    //             'success' => true,
    //             'msg_to_show'    =>  $msg_to_show,
    //             'show_confirm_decline_buttons' => 0,
    //             'message' => 'unlinked_for_tenant_view',
    //             'status'  => 200
    //         ];
    //         return response()->json($response, 200);
    //     }

    //     // API payments = select active payments (where payment status not in (voided, settled or Payment in Default) where payment tenant = tenant ID,
    //     /* 1.upcoming cheque 2.voided 3.settled  4.Overdue 5.cheque returned
    //     6.voided  7.upcoming Payment 8.overdue 9.paid 10. payment in default*/
    //     $payments = \DB::table('payments')
    //         ->where('tenant_id', $request->tenant_id)
    //         ->whereNotIn('status', [3, 2, 6, 9, 10])
    //         ->select('id', 'payment_type', 'status', 'amount', 'payment_date', 'remark', 'payment_code', 'cheque_no')
    //         ->get();

    //     if (!blank($payments)) {
    //         $msg_to_show = 'Active payments found for this tenant, please void, delete or change payment status to “payment on default or Settled” and then try to disconnect again';
    //         $response = [
    //             'success' => true,
    //             'msg_to_show'    =>  $msg_to_show,
    //             'show_confirm_decline_buttons' => 0,
    //             'message' => 'unlinked_for_tenant_view',
    //             'status'  => 200
    //         ];
    //         return response()->json($response, 200);
    //     }

    //     $msg_to_show = 'Are you sure you want to disconnect the tenant from linked units?';
    //     $response = [
    //         'success' => true,
    //         'msg_to_show'    =>  $msg_to_show,
    //         'show_confirm_decline_buttons' => 1,
    //         'message' => 'unlinked_for_tenant_view',
    //         'status'  => 200
    //     ];
    //     return response()->json($response, 200);
    // }//unlinked_for_tenant_view //show pop up


    // POST (CASE->4.2)
    // public function unlinked_confirm_decline_for_tenant_view(Request $request)
    // {
    //     $validator = validator($request->all(), [
    //         'tenant_id' => 'required|numeric|exists:tenants,id',
    //         'accept_decline' => 'required|in:1',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
    //     }

    //     if ($request->accept_decline == 1) {

    //         \DB::table('tenants_units')
    //             ->where('tenant_id',  $request->tenant_id)
    //             ->update(['tenant_id' => 0]);

    //         \DB::table('tenants')
    //             ->where('id',  $request->tenant_id)
    //             ->update(['status' => 3]);
    //     }
    //     $response = [
    //         'success' => true,
    //         'message' => 'Unlinked Successfully',
    //         'status'  => 200
    //     ];
    //     return response()->json($response, 200);
    // }//unlinked_confirm_decline_for_tenant_view


    //  POST
    // accept from pop up
    public function accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit(Request $request)
    {
        // \Log::notice($request->all());
        $validator = validator($request->all(), [
            'tenant_id'  => 'required|numeric|exists:tenants,id',
            'accept_decline' => 'required|numeric|in:1',
            'tenant_unit_id' => 'required|numeric|exists:tenants_units,id',
            'case' => 'required|in:2.1,2.2,2.3'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $msg_to_show = '';
        $can_submit = 1;

        if ($request->case == '2.1') {

            // contract details for log
            $contract_detailes = \DB::table('contracts_tables')
                ->where('unit_id', $request->tenant_unit_id)
                ->where('end_date', '>', \DB::raw('CURDATE()'))
                ->where('Tenant_id', 0)
                ->select('Tenant_id', 'id')
                ->first();

            $tenant = \DB::table('tenants')
                ->where('id', $contract_detailes->Tenant_id)
                ->select('first_name', 'last_name')
                ->first();

            $tenant_name = $tenant->first_name . $tenant->last_name;

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('contract', 'edit', $contract_detailes->id, $request->user()->id, $request->user()->pm_company_id, $tenant_name, 'contract_edit');

            if ($request->accept_decline == 1) {
                \DB::table('contracts_tables')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('end_date', '>', \DB::raw('CURDATE()'))
                    ->where('Tenant_id', 0)
                    ->update(['Tenant_id' => $request->tenant_id]);
            } else {
                $response = [
                    'success' => true,
                    'can_submit'  => 0,
                    // 'msg_to_show' => 'Please delete the active contract record first then try to link the tenant to the unit, unit has not been edited/saved',
                    'msg_to_show' => __(app()->getLocale() . '.delete_active_contract,'),
                    'message' => 'accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit',
                    'status'  => 200
                ];
                return response()->json($response, 200);
            }
        } elseif ($request->case == '2.2') {

            if ($request->accept_decline == 1) {

                //payment details for log.
                $tenant_id = \DB::table('payments')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('tenant_id', 0)
                    ->select('tenant_id', 'id')
                    ->first();

                $temp_data = \DB::table('tenants')
                    ->where('id', $tenant_id->tenant_id)
                    ->select('first_name', 'last_name')
                    ->first();

                $tenant_name = $temp_data->first_name . $temp_data->last_name;

                //module,action,affected_record_id,pm_id,pm_company_id
                \App\Services\PmLogService::pm_log_entry('payments', 'edit', $tenant_id->id, $request->user()->id, $request->user()->pm_company_id, $tenant_name, 'payment_edit');

                \DB::table('payments')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('tenant_id', 0)
                    ->update(['tenant_id' => $request->tenant_id]);

                // contract details for log
                $contract_detailes = \DB::table('contracts_tables')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('end_date', '>', \DB::raw('CURDATE()'))
                    ->where('Tenant_id', 0)
                    ->select('Tenant_id', 'id')
                    ->first();

                $tenant = \DB::table('tenants')
                    ->where('id', $contract_detailes->Tenant_id)
                    ->select('first_name', 'last_name')
                    ->first();

                $tenant_name = $tenant->first_name . $tenant->last_name;

                //module,action,affected_record_id,pm_id,pm_company_id
                \App\Services\PmLogService::pm_log_entry('contract', 'edit', $contract_detailes->id, $request->user()->id, $request->user()->pm_company_id, $tenant_name, 'contract_edit');

                \DB::table('contracts_tables')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('end_date', '>', \DB::raw('CURDATE()'))
                    ->where('Tenant_id', 0)
                    ->update(['Tenant_id' => $request->tenant_id]);
            } else {
                $response = [
                    'success' => true,
                    'can_submit'  => 0,
                    // 'msg_to_show' => 'Please delete the payments records first then try to create/edit the unit record again, unit is not created/saved',
                    'msg_to_show' => __(app()->getLocale() . '.delete_payments_records_first,'),
                    'message' => 'accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit',
                    'status'  => 200
                ];
                return response()->json($response, 200);
            }
        } elseif ($request->case == '2.3') {

            if ($request->accept_decline == 1) {

                //payment details for log.
                $tenant_id = \DB::table('payments')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('tenant_id', 0)
                    ->select('tenant_id', 'id')
                    ->first();

                $temp_data = \DB::table('tenants')
                    ->where('id', $tenant_id->tenant_id)
                    ->select('first_name', 'last_name')
                    ->first();

                $tenant_name = $temp_data->first_name . $temp_data->last_name;

                //module,action,affected_record_id,pm_id,pm_company_id
                \App\Services\PmLogService::pm_log_entry('payments', 'edit', $tenant_id->id, $request->user()->id, $request->user()->pm_company_id, $tenant_name, 'payment_edit');


                \DB::table('payments')
                    ->where('unit_id', $request->tenant_unit_id)
                    ->where('tenant_id', 0)
                    ->update(['tenant_id' => $request->tenant_id]);

            } else {
                $response = [
                    'success' => true,
                    'can_submit'  => 0,
                    // 'msg_to_show' => 'Please delete the payments records first then try to create/edit the unit record again, unit is not created/saved',
                    'msg_to_show' => __(app()->getLocale() . '.delete_payments_records_first,'),
                    'message' => 'accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit',
                    'status'  => 200
                ];
                return response()->json($response, 200);
            }
        }

        $response = [
            'success' => true,
            'can_submit'  => $can_submit,
            'msg_to_show' => $msg_to_show,
            'message' => 'accept_decline_on_change_tenant_drop_down_of_tenant_unit_edit',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }
}
