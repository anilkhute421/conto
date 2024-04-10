<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintanceRequestForModel;
use App\Http\Controllers\Api\ApiBaseController;
use App\Models\MaintanceRequestModel;
use App\Models\MaintanceExpertModel;
use App\Models\ExpenesItemFilesModel;


class MaintanenceTwoController extends ApiBaseController
{

    // POST
    //PM
    // status should be as below
    //-	Request raised (1)
    //- Request assigned (2)
    //-	Request completed (3)
    //-	Request is on hold (4)
    //-	Request canceled (5)
    public function maintanence_request_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2,3,4,5', // 0 all
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

            $search_expert_maintanence_request->select(
                'maintance_requests.id',
                'maintance_requests.maintenance_request_id',
                'maintance_requests.unit_id',
                'maintance_requests.building_id',
                'maintance_requests.tenant_id',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',
                'maintance_requests.property_manager_id',
                'experts.name',
                'tenants.first_name',
                'tenants.last_name',
                'maitinance_request_for.maitinance_request_name',
                'buildings.building_name',
                'tenants_units.unit_no',
            )
            ->take(10)
            ->skip($skip);

            $all_data = $search_expert_maintanence_request
            ->groupBy('maintance_requests.id')
            ->orderBy('maintance_requests.id','DESC')
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

                $item->pm_can_edit = 1;

                if($item->property_manager_id == 0){
                    $item->pm_can_edit = 0;
                }

                unset($item->created_at);
                unset($item->maintenance_request_id);
                // unset($item->unit_id);
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

        $count = $search_expert_maintanence_request_count
        ->distinct('maintance_requests.id')
        ->count();


        $response = [
            'success' => true,
            'data'    => $all_data,
            'message' => 'maintanence_request_list_by_company_id',
            'pagecount'  => (int)ceil($count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // POST
    //PM
    // status should be as below
    //-	Request raised (1)
    //- Request assigned (2)
    //-	Request completed (3)
    //-	Request is on hold (4)
    //-	Request canceled (5)
    public function unread_comment_maintenance_req_list(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2,3,4,5', // 0 all
            'maintenance_request_for_id' => 'required|numeric',// 0 all
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
            'date_to'  => 'nullable|date',
            'dropdown'  => 'required|in:bycount,bydate', //order bycount , order bydate listing.


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

        \DB::statement("SET SQL_MODE=''");

        $search_expert_maintanence_request = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->leftjoin('maintenance_experts', 'maintance_requests.id', '=', 'maintenance_experts.maintenance_id')
            ->leftjoin('maitinance_request_for', 'maintance_requests.maintenance_request_id', '=', 'maitinance_request_for.id')
            ->leftjoin('experts', 'maintenance_experts.expert_id', '=', 'experts.id')
            ->leftjoin('tenants', 'maintance_requests.tenant_id', '=', 'tenants.id')

            ->where('maintance_requests.pm_company_id', $request->user()->pm_company_id)
            ->where('maintance_requests.tenant_unread_count', '!=', 0);
 
            if($request->dropdown == 'bycount'){
                $search_expert_maintanence_request->orderBy('maintance_requests.tenant_unread_count', 'DESC');
            }elseif($request->dropdown == 'bydate'){
                $search_expert_maintanence_request->orderBy('maintance_requests.tenant_unread_date', 'DESC');
            }
            

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

            $search_expert_maintanence_request->select(
                'maintance_requests.id',
                'maintance_requests.maintenance_request_id',
                'maintance_requests.unit_id',
                'maintance_requests.building_id',
                'maintance_requests.tenant_id',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',
                'maintance_requests.property_manager_id',
                'maintance_requests.tenant_unread_count',
                'maintance_requests.tenant_unread_date',
                'experts.name',
                'tenants.first_name',
                'tenants.last_name',
                'maitinance_request_for.maitinance_request_name',
                'buildings.building_name',
                'tenants_units.unit_no',
            )
            ->take(10)
            ->skip($skip);

            $all_data = $search_expert_maintanence_request
            ->groupBy('maintance_requests.id')
            // ->orderBy('maintance_requests.id','DESC')
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

                $item->pm_can_edit = 1;

                if($item->property_manager_id == 0){
                    $item->pm_can_edit = 0;
                }

                unset($item->created_at);
                unset($item->maintenance_request_id);
                // unset($item->unit_id);
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

            ->where('maintance_requests.pm_company_id', $request->user()->pm_company_id)
            ->where('maintance_requests.tenant_unread_count', '!=', 0);

            if($request->dropdown == 'bycount'){
                $search_expert_maintanence_request_count->orderBy('maintance_requests.tenant_unread_count', 'DESC');
            }elseif($request->dropdown == 'bydate'){
                $search_expert_maintanence_request_count->orderBy('maintance_requests.tenant_unread_date', 'DESC');
            }

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

        $count = $search_expert_maintanence_request_count
        ->distinct('maintance_requests.id')
        ->count();

        $response = [
            'success' => true,
            'data'    => $all_data,
            'message' => 'unread_comment_maintenance_req_list',
            'pagecount'  => (int)ceil($count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



     //item wise
     //PM
     // expense_line_id 0 all
     public function expenses_list_by_company_id(Request $request){
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'expense_line_id' => 'required|numeric',
            'date_from'  => 'nullable|date',
            'date_to'  => 'nullable|date',
            'amount_from'  => 'nullable|numeric',
            'amount_to'  => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        if ( ($request->expense_line_id != 0) ) {
            $validator = validator($request->all(), [
                'expense_line_id' => 'exists:expenseslines,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }


        \DB::statement("SET SQL_MODE=''");
        $all_data_query = \DB::table('expenses_items')
            ->leftJoin('expenses', 'expenses_items.expenses_id', '=', 'expenses.id')
            ->leftJoin('maintance_requests', 'expenses.request_id', '=', 'maintance_requests.id')
            ->leftJoin('currencies', 'expenses_items.currency_id', '=', 'currencies.id')
            ->leftJoin('expenseslines', 'expenses_items.expenses_lines_id', '=', 'expenseslines.id')
            ->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

            ->where('expenses.pm_company_id', $request->user()->pm_company_id);

        if($request->expense_line_id != 0){
            $all_data_query->where('expenseslines.id', $request->expense_line_id);
        }

        //amount between--------------------------
        if(!blank($request->amount_from) && !blank($request->amount_to)){
            $startCost = $request->amount_from;
            $endCost = $request->amount_to;
            if($startCost == $endCost){
                $all_data_query->where('expenses_items.cost', '=', $endCost);
            }else{
                $all_data_query->whereBetween('expenses_items.cost', [$startCost, $endCost]);
            }
        }elseif(!blank($request->amount_from)){
            $startCost = $request->amount_from;
            $all_data_query->where('expenses_items.cost', '>=', $startCost);
        }elseif(!blank($request->amount_to)){
            $endCost = $request->amount_to;
            $all_data_query->where('expenses_items.cost', '<=', $endCost);
        }

        //date between-----------------------------
        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $all_data_query->whereDate('expenses_items.date', '=', $endDate);
            }else{
                $all_data_query->whereBetween('expenses_items.date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $all_data_query->whereDate('expenses_items.date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $all_data_query->whereDate('expenses_items.date', '<=', $endDate);
        }

        $all_data_query->select(
            'expenses_items.cost',
            'expenses_items.date',
            'currencies.symbol',
            'currencies.currency',
            'expenseslines.expenseslines_name',
            'maintance_requests.request_code',
            'expenses.id',
            'buildings.building_name',
            'tenants_units.unit_no',
            'expenses_items.id as expense_item_id',
        )
        ->take(10)
        ->skip($skip);

        $all_data = $all_data_query->groupBy('expenses_items.id')->get()
            ->transform(function ($item) {

                $item->amount = $item->symbol . $item->cost;
                $item->date = date('d M Y', strtotime($item->date));
                $item->expenses = $item->expenseslines_name;

                unset($item->cost);
                unset($item->symbol);
                unset($item->expenseslines_name);

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


        $count_query =  \DB::table('expenses_items')
        ->leftJoin('expenses', 'expenses_items.expenses_id', '=', 'expenses.id')
        ->leftJoin('maintance_requests', 'expenses.request_id', '=', 'maintance_requests.id')
        ->leftJoin('currencies', 'expenses_items.currency_id', '=', 'currencies.id')
        ->leftJoin('expenseslines', 'expenses_items.expenses_lines_id', '=', 'expenseslines.id')
        ->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
        ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

        ->where('expenses.pm_company_id', $request->user()->pm_company_id);

        if($request->expense_line_id != 0){
            $count_query->where('expenseslines.id', $request->expense_line_id);
        }


        //amount between--------------------------
        if(!blank($request->amount_from) && !blank($request->amount_to)){
            $startCost = $request->amount_from;
            $endCost = $request->amount_to;
            if($startCost == $endCost){
                $count_query->where('expenses_items.cost', '=', $endCost);
            }else{
                $count_query->whereBetween('expenses_items.cost', [$startCost, $endCost]);
            }
        }elseif(!blank($request->amount_from)){
            $startCost = $request->amount_from;
            $count_query->where('expenses_items.cost', '>=', $startCost);
        }elseif(!blank($request->amount_to)){
            $endCost = $request->amount_to;
            $count_query->where('expenses_items.cost', '<=', $endCost);
        }

        //date between-----------------------------
        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $count_query->whereDate('expenses_items.date', '=', $endDate);
            }else{
                $count_query->whereBetween('expenses_items.date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $count_query->whereDate('expenses_items.date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $count_query->whereDate('expenses_items.date', '<=', $endDate);
        }


        $count = $count_query
        ->distinct('expenses_items.id')
        ->count();


        $response = [
            'success' => true,
            'data'    => $all_data,
            'message' => 'expenses_list_by_company_id',
            'pagecount'  => (int)ceil($count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // view each item of expense
    public function view_expenses_by_id(Request $request){

        $validator = validator($request->all(), ['expense_item_id' => 'required|numeric|exists:expenses_items,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expense_item_id = $request->expense_item_id;
        $expense_id = \DB::table('expenses_items')->where('id', $request->expense_item_id)->value('expenses_id');

        $all_data_query = \DB::table('expenses')->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('expenses.id', $expense_id);

            $all_data_query->select(
                'expenses.id',
                'expenses.request_id',
                'expenses.tenant_id',
                'expenses.unit_id',
                'expenses.building_id',

                'buildings.building_name',
                'tenants_units.unit_no',
            );

            $all_data = $all_data_query->first();

            $_temp_request_code = \DB::table('maintance_requests')->where('id', $all_data->request_id)->select('request_code')->first();
            $all_data->request_code = !blank($_temp_request_code) ? $_temp_request_code->request_code : '';

            if($all_data->tenant_id != 0){
                //get tenant details
                $tenant_details = \DB::table('tenants')->where('id', $all_data->tenant_id)->select('last_name','first_name')->first();

                $all_data->tenant = !blank($tenant_details) ? $tenant_details->first_name.' '.$tenant_details->last_name : '';
            }else{
                $all_data->tenant = '';
            }


            //files with model m accessor
            $expense_items = \DB::table('expenses_items')
                ->where('expenses_id', $expense_id)
                ->where('id', $expense_item_id)
                ->select('currency_id','cost','date','expenses_lines_id','id','description')
                ->get()
                ->transform(function ($item) {
                    $symbol = '';
                    $currency = \DB::table('currencies')->where('id', $item->currency_id)->select('symbol','currency','id')->first();
                    $symbol = !blank($currency) ? $currency->symbol : '';
                    $item->amount = $symbol.$item->cost;
                    $item->currency_name = $currency->currency;
                    $item->currency_id = $currency->id;

                    $item->date_for_edit = $item->date;

                    $item->date = date('d M Y', strtotime($item->date));

                    $item->request_for = \DB::table('expenseslines')
                    ->where('id', $item->expenses_lines_id)
                    ->value('expenseslines_name');

                    $item->files = ExpenesItemFilesModel::where('expense_item_id', $item->id)
                    ->select('file_name','id')
                    ->get();

                    return $item;
                });

        $response = [
            'success' => true,
            'data'    => $all_data,
            'expense_items'    => $expense_items,
            'message' => 'view_expenses_by_id',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



}
