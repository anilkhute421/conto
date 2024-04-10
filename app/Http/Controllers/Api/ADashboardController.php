<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiBaseController;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use App\Helpers\DrillDownExportHelper;
use DB;


class ADashboardController extends ApiBaseController
{
    //POST
    public function dashboard_counts_or_dropdown(Request $request){

        $pm_company_id = $request->user()->pm_company_id;

        $active_buildings_count  = DB::select("CALL TotalActiveBuildings(".$pm_company_id.")");

        $active_units_count  = DB::select("CALL TotalActiveUnits(".$pm_company_id.")");

        $active_tenants_count  = DB::select("CALL TotalLinkedTenants(".$pm_company_id.")");

        $active_avaliable_units_count  = DB::select("CALL TotalActiveAvalUnits(".$pm_company_id.")");

        $_TotalContractsExpiring2m_count  = DB::select("CALL TotalContractsExpiring2m(".$pm_company_id.")");


        $owner_dropdown = \DB::table('owners')
            ->where('pm_company_id', $request->user()->pm_company_id)
            ->select('id','name')
            ->get();

        $buildings_dropdown = \DB::table('buildings')
            ->where('pm_company_id', $request->user()->pm_company_id)
            ->select('id','building_name')
            ->get()
            ->transform(function ($row) {

                $row->name = $row->building_name;
               unset($row->building_name);
                return $row;
            });

        $tenants_dropdown = \DB::table('tenants')
            ->where('pm_company_id', $request->user()->pm_company_id)
            ->select('id','first_name','last_name')
            ->get()
            ->transform(function ($row) {

                $row->name = $row->first_name . ' ' . $row->last_name;
               unset($row->first_name);
               unset($row->last_name);

                return $row;
            });

        $response = [
            'success' => true,
            'active_buildings_count' =>   $active_buildings_count[0]->TotalActiveBuildings,
            'active_units_count' =>   $active_units_count[0]->TotalActiveUnits,
            'active_tenants_count' =>   $active_tenants_count[0]->TotalLinkedTenants,
            'active_avaliable_units_count' =>   $active_avaliable_units_count[0]->TotalActiveAvalUnits,
            'total_contracts_expiring2m_count' => $_TotalContractsExpiring2m_count[0]->TotalContractsExpiring,
            'owner_dropdown' =>   $owner_dropdown,
            'buildings_dropdown' =>   $buildings_dropdown,
            'tenants_dropdown' =>   $tenants_dropdown,
            'message' => 'dashboard_counts_or_dropdown',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    public function dashboard_filters(Request $request){

        $pm_company_id = $request->user()->pm_company_id;

        $validator = validator($request->all(), [
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {

            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);

            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            if($request->month_id == 12){

                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_opne_record =   DB::select("CALL TotalOpenRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_settled_record =   DB::select("CALL TotalSetPayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_overdue_record =   DB::select("CALL TotalOverDuePayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_upcoming_record =  DB::select("CALL TotalUpcomingPayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_expenses_record = DB::select("CALL TotalExpensesAmount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");

            }elseif($request->month_id == 9){

                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_opne_record = DB::select("CALL TotalOpenRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_settled_record = DB::select("CALL TotalSetPayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_overdue_record = DB::select("CALL TotalOverDuePayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_upcoming_record = DB::select("CALL TotalUpcomingPayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_expenses_record = DB::select("CALL TotalExpensesAmount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");


            }elseif($request->month_id == 6){

                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_opne_record = DB::select("CALL TotalOpenRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_settled_record = DB::select("CALL TotalSetPayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_overdue_record = DB::select("CALL TotalOverDuePayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_upcoming_record = DB::select("CALL TotalUpcomingPayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_expenses_record = DB::select("CALL TotalExpensesAmount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");


            }elseif($request->month_id == 1){

                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_opne_record = DB::select("CALL TotalOpenRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_settled_record = DB::select("CALL TotalSetPayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_overdue_record = DB::select("CALL TotalOverDuePayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_payment_upcoming_record = DB::select("CALL TotalUpcomingPayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_maintenace_expenses_record = DB::select("CALL TotalExpensesAmount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");

            }
        }
        else{

            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);

            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }


            $_maintenace_closed_record = DB::select("CALL TotalClosedRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_maintenace_opne_record = DB::select("CALL TotalOpenRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_payment_settled_record = DB::select("CALL TotalSetPaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_payment_overdue_record = DB::select("CALL TotalOverDuePaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_payment_upcoming_record = DB::select("CALL TotalUpcomingPaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_maintenace_expenses_record = DB::select("CALL TotalExpensesAmountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");

        }

        $response = [
            'success' => true,
            'maintenace_closed_record' =>   $_maintenace_closed_record[0]->TotalPaymentsAmount,
            'maintenace_opne_record' =>   $_maintenace_opne_record[0]->TotalPaymentsAmount,
            'payment_settled_record' =>   $_payment_settled_record[0]->TotalPaymentsAmount,
            'payment_overdue_record' =>   $_payment_overdue_record[0]->TotalPaymentsAmount,
            'payment_upcoming_record' =>   $_payment_upcoming_record[0]->TotalPaymentsAmount,
            'maintenace_expenses_record' =>   $_maintenace_expenses_record[0]->TotalExpensesAmount,
            'message' => 'dashboard_filters',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    //for single card on dashboard
    //2 months
    public function drill_down_list_contract_expiring2m(Request $request){

        $pm_company_id = $request->user()->pm_company_id;

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $offset = $page ? 10 * ($page - 1) : 0;
        $limit = 10;

        $list = DB::select("CALL DrillDownTotalContractsExpiring2M(".$pm_company_id.",".$limit.",".$offset.")");


        $_total  = DB::select("CALL TotalContractsExpiring2m(".$pm_company_id.")");
        $_total = $_total[0]->TotalContractsExpiring;

        $response = [
            'success' => true,
            'list' =>   $list,
            'pagecount'  => (int)ceil($_total / 10),
            'message' => 'drill_down_list_contract_expiring2m',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    public function drill_down_list_by_card(Request $request){

        $pm_company_id = $request->user()->pm_company_id;

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'card_name' => 'required|in:closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $offset = $page ? 10 * ($page - 1) : 0;
        $limit = 10;

        if ($request->tenant_id != 0) {
            $tenant_check = \DB::table('tenants')->where('id', $request->tenant_id)->select('id')->first();
            if (blank($tenant_check)) {
                return $this->sendSingleFieldError('tenant_id is invalid', 201, 200);
            }
        }

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {

            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);

            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            if($request->month_id == 12){

                if($request->card_name == 'closed_requests'){
                   $list = DB::select("CALL DrillDownTotalClosedRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                   .",".$limit.",".$offset.")");
                   $_total = DB::select("CALL TotalClosedRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                   $_total = $_total[0]->TotalPaymentsAmount;
                }
                elseif($request->card_name == 'open_requests'){
                    $list = DB::select("CALL DrillDownTotalOpenRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total =   DB::select("CALL TotalOpenRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPaymentsAmount;

                }
                elseif($request->card_name == 'upcoming_payment'){
                    $list =   DB::select("CALL DrillDownTotalUpcomingPayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total =  DB::select("CALL TotalUpcomingPaymentsCount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPayments;

                }
                elseif($request->card_name == 'overdue_payment'){
                    $list =   DB::select("CALL DrillDownTotalOverDuePayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total = DB::select("CALL TotalOverDuePaymentsCount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPaymentsAmount;

                }
                elseif($request->card_name == 'settled_payment'){
                    $list =   DB::select("CALL DrillDownTotalSetPayments12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total =   DB::select("CALL TotalSetPaymentsCount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPayments;

                }
                elseif($request->card_name == 'expenses_amount'){
                    $list =   DB::select("CALL DrillDownTotalExpensesAmount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total = DB::select("CALL TotalExpensesCount12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalExpenses;
                }


            }elseif($request->month_id == 9){

                if($request->card_name == 'closed_requests'){
                    $list = DB::select("CALL DrillDownTotalClosedRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total = DB::select("CALL TotalClosedRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPaymentsAmount;
                }
                 elseif($request->card_name == 'open_requests'){
                     $list =   DB::select("CALL DrillDownTotalOpenRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOpenRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPaymentsAmount;
                    }
                 elseif($request->card_name == 'upcoming_payment'){
                     $list =   DB::select("CALL DrillDownTotalUpcomingPayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalUpcomingPaymentsCount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'overdue_payment'){
                     $list =   DB::select("CALL DrillDownTotalOverDuePayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOverDuePaymentsCount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'settled_payment'){
                     $list =   DB::select("CALL DrillDownTotalSetPayments9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalSetPaymentsCount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'expenses_amount'){
                     $list =   DB::select("CALL DrillDownTotalExpensesAmount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalExpensesCount9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalExpenses;
                    }


            }elseif($request->month_id == 6){

                if($request->card_name == 'closed_requests'){
                    $list = DB::select("CALL DrillDownTotalClosedRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total = DB::select("CALL TotalClosedRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPaymentsAmount;
                }
                 elseif($request->card_name == 'open_requests'){
                     $list =   DB::select("CALL DrillDownTotalOpenRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOpenRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPaymentsAmount;
                    }
                 elseif($request->card_name == 'upcoming_payment'){
                     $list =   DB::select("CALL DrillDownTotalUpcomingPayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalUpcomingPaymentsCount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'overdue_payment'){
                     $list =   DB::select("CALL DrillDownTotalOverDuePayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOverDuePaymentsCount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'settled_payment'){
                     $list =   DB::select("CALL DrillDownTotalSetPayments6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalSetPaymentsCount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'expenses_amount'){
                     $list =   DB::select("CALL DrillDownTotalExpensesAmount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalExpensesCount6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalExpenses;
                    }


            }elseif($request->month_id == 1){

                if($request->card_name == 'closed_requests'){
                    $list = DB::select("CALL DrillDownTotalClosedRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                    .",".$limit.",".$offset.")");
                    $_total = DB::select("CALL TotalClosedRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                    $_total = $_total[0]->TotalPaymentsAmount;
                }
                 elseif($request->card_name == 'open_requests'){
                     $list =   DB::select("CALL DrillDownTotalOpenRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOpenRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPaymentsAmount;
                    }
                 elseif($request->card_name == 'upcoming_payment'){
                     $list =   DB::select("CALL DrillDownTotalUpcomingPayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalUpcomingPaymentsCount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'overdue_payment'){
                     $list =   DB::select("CALL DrillDownTotalOverDuePayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalOverDuePaymentsCount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'settled_payment'){
                     $list =   DB::select("CALL DrillDownTotalSetPayments1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalSetPaymentsCount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalPayments;
                    }
                 elseif($request->card_name == 'expenses_amount'){
                     $list =   DB::select("CALL DrillDownTotalExpensesAmount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id
                     .",".$limit.",".$offset.")");
                     $_total = DB::select("CALL TotalExpensesCount1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                     $_total = $_total[0]->TotalExpenses;
                    }

            }
        }
        else{

            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);

            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            if($request->card_name == 'closed_requests'){
                $list = DB::select("CALL DrillDownTotalClosedRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                .",".$limit.",".$offset.")");
                $_total = DB::select("CALL TotalClosedRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                $_total = $_total[0]->TotalPaymentsAmount;

            }
             elseif($request->card_name == 'open_requests'){
                $list = DB::select("CALL DrillDownTotalOpenRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                .",".$limit.",".$offset.")");
                $_total = DB::select("CALL TotalOpenRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                $_total = $_total[0]->TotalPaymentsAmount;

            }
             elseif($request->card_name == 'upcoming_payment'){
                $list = DB::select("CALL DrillDownTotalUpcomingPaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                .",".$limit.",".$offset.")");
                $_total = DB::select("CALL TotalUpcomingPaymentsCountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                $_total = $_total[0]->TotalPayments;

            }
             elseif($request->card_name == 'overdue_payment'){
                 $list = DB::select("CALL DrillDownTotalOverDuePaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                 .",".$limit.",".$offset.")");
                 $_total = DB::select("CALL TotalOverDuePaymentsCountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                 $_total = $_total[0]->TotalPayments;

                }
             elseif($request->card_name == 'settled_payment'){
                 $list = DB::select("CALL DrillDownTotalSetPaymentsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                 .",".$limit.",".$offset.")");
                 $_total = DB::select("CALL TotalSetPaymentsCountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                 $_total = $_total[0]->TotalPayments;

                }
             elseif($request->card_name == 'expenses_amount'){
                $list = DB::select("CALL DrillDownTotalExpensesAmountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'"
                .",".$limit.",".$offset.")");
                $_total = DB::select("CALL TotalExpensesCountDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
                $_total = $_total[0]->TotalExpenses;
            }
        }

        $response = [
            'success' => true,
            'list' =>   $list,
            'pagecount'  => (int)ceil($_total / 10),
            'message' => 'drill_down_list_by_card',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //------------------------------------------- excel exports ----------------------------------------
    //GET web.php
    //for single card on dashboard
    //2 months
    public function excel_export_contract_expiring2m(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $pm_id = $request->pm_id;

        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        $_total  = DB::select("CALL TotalContractsExpiring2m(".$pm_company_id.")");
        $_total = $_total[0]->TotalContractsExpiring;
        $_loop_count = (int)ceil($_total / 10);

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_contract_expiring2m($writer, $pm_company_id, $_loop_count);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_contract_expiring2m ------------'.json_encode($e));
        }
    }


    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_closed_request(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_closed_request($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_closed_request ------------'.json_encode($e));
        }
    }



    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_open_request(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_open_request($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_open_request ------------'.json_encode($e));
        }
    }



    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_set_payment(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_set_payment($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_set_payment ------------'.json_encode($e));
        }
    }



    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_overdue_payment(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_overdue_payment($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_overdue_payment ------------'.json_encode($e));
        }
    }



    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_upcoming_payment(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_upcoming_payment($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_upcoming_payment ------------'.json_encode($e));
        }
    }



    //GET web.php
    //closed_requests,open_requests,upcoming_payment,overdue_payment,settled_payment,expenses_amount
    public function excel_export_card_maintenance_expense(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'pm_id' => 'required|numeric|exists:property_managers,id',
            'time_key' => 'required|in:month,date_range',
            'owner_id' => 'required|numeric',
            'building_id' => 'required|numeric',
            'tenant_id' => 'required|numeric',
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

        if ($request->owner_id != 0) {
            $owner_check = \DB::table('owners')->where('id', $request->owner_id)->select('id')->first();
            if (blank($owner_check)) {
                return $this->sendSingleFieldError('owner_id is invalid', 201, 200);
            }
        }

        if ($request->building_id != 0) {
            $building_check = \DB::table('buildings')->where('id', $request->building_id)->select('id')->first();
            if (blank($building_check)) {
                return $this->sendSingleFieldError('building_id is invalid', 201, 200);
            }
        }

        if ($request->tenant_id == 0) {

            $request->tenant_id = 'NULL';
        }
        if ($request->owner_id == 0) {

            $request->owner_id = 'NULL';
        }
        if ($request->building_id == 0) {

            $request->building_id = 'NULL';
         }

        if ($request->time_key == 'month') {
            $validator = validator($request->all(), [
                'month_id' => 'required|in:12,9,6,1',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }else{
            $validator = validator($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $pm_id = $request->pm_id;
        $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

        try{
            $writer = WriterEntityFactory::createXLSXWriter();

            $writer = DrillDownExportHelper::excel_export_card_maintenance_expense($writer, $pm_company_id, $request);

            $writer->close();
        }catch(\Exception $e){
            \Log::error('------------- excel_export_card_maintenance_expense ------------'.json_encode($e));
        }
    }





}
