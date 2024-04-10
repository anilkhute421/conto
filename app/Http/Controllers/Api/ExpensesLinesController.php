<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenesLinesModel;
use App\Models\MaintanceRequestModel;
use App\Models\ExpenesModel;
use App\Models\TenantNotificationModel;
use App\Models\PmNotificationModel;
use App\Models\ExpenesItemFilesModel;
use App\Models\ExpenesItemModel;
use App\Http\Requests\PmRequest;
use App\Http\Controllers\Api\ApiBaseController;

class ExpensesLinesController extends ApiBaseController
{
    //GET
    public function expenes_dropdown(Request $request)
    {
        $lang = app()->getLocale();

        if($lang == 'en'){

            $expenses = ExpenesLinesModel::select('expenseslines_name', 'id')->get();
        }
        elseif($lang == 'ar'){

            $expenses = ExpenesLinesModel::select('arabic_expenseslines_name as expenseslines_name', 'id')->get();
        }
        return $this->sendResponse($expenses, 'expenseslines_dropdown', 200, 200);
    }

    //GET
    public function request_dropdown(Request $request)
    {

        $validator = validator($request->all(), [
            'unit_id' => 'required|numeric|exists:tenants_units,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $request_id = MaintanceRequestModel::where('pm_company_id', $request->user()->pm_company_id)
            ->where('unit_id', $request->unit_id)
            ->select('request_code', 'id')
            ->get();
        return $this->sendResponse($request_id, 'request_dropdown', 200, 200);
    }

    //POST
    public function add_expense(Request $request)
    {
        // \Log::notice('add_expense');
        \Log::notice($request->all());

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), PmRequest::add_expense());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ($request->tenant_id != 0) {
            $tenant_check = \DB::table('tenants')->where('id', $request->tenant_id)->select('id')->first();
            if (blank($tenant_check)) {
                return $this->sendSingleFieldError('tenant_id is invalid', 201, 200);
            }
        }

        //arraY validate (expenses items)
        $validator = validator($request->all(), [
            "expenses_lines_ids"    => "required|array|min:1|max:3",
            "expenses_lines_ids.*"  => 'required|numeric|exists:expenseslines,id',

            "currency_ids"    => "required|array|min:1|max:3",
            "currency_ids.*"  => 'required|numeric|exists:currencies,id',

            "expenses_amounts"    => "required|array|min:1|max:3",
            "expenses_amounts.*"  => 'required|digits_between:1,8',

            "dates"    => "required|array|min:1|max:3",
            "dates.*"  => 'required|date',

            "description"   => "required|array|min:1|max:3",
            "description.*"   => "nullable",

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expenes = ExpenesModel::create([
            'building_id' => $request->building_id,
            'unit_id' => $request->unit_id,
            'request_id' => $request->request_id,
            'tenant_id' => $request->tenant_id,
            'pm_company_id' => $request->user()->pm_company_id,
        ]);

        $i = 0;
        //3 expense items
        foreach ($request->expenses_lines_ids as $value) {
            // \Log::notice('item ka loop');

            $desc = blank( $request->description[$i] ) ? '' : $request->description[$i];

            $created_item = ExpenesItemModel::create([
                'expenses_id' => $expenes->id,
                'expenses_lines_id' => $request->expenses_lines_ids[$i],
                'currency_id' => $request->currency_ids[$i],
                'cost' => $request->expenses_amounts[$i],
                'date' => $request->dates[$i],
                'description' => $desc,
            ]);

            $old_expnese_lines_name = \DB::table('expenseslines')->where('id', $request->expenses_lines_ids[$i])->value('expenseslines_name');

             //module,action,affected_record_id,pm_id,pm_company_id,
            \App\Services\PmLogService::pm_log_entry('expense','create',$created_item->id,$request->user()->id,$request->user()->pm_company_id,$old_expnese_lines_name, 'expenes_added');

            if(!blank($request->file('files_array')) && array_key_exists($i,$request->file('files_array'))){

            $j = 0;
            // 3 files of items.
            foreach ($request->file('files_array')[$i] as $index => $file) {

                $filename =  $expenes->id . uniqid() . '.' . $file->getClientOriginalExtension();
                try {
                    \Storage::disk('azure_documents')->put($filename, \File::get($file));
                    \App\models\ExpenesItemFilesModel::create(['expense_item_id' => $created_item->id, 'file_name' => $filename]);
                } catch (\Exception $e) {
                    \Log::error('---------add_expense---------- ' . $e);
                }
                $j++;
            }

        }
            $i++;
        }


        //    return $this->sendResponse([], 'Expense added successfully.', 200, 200);
        return $this->sendResponse([], __(app()->getLocale() . '.expense_added'), 200, 200);
    }


    //POST
    // single item updating
    public function update_expenses(Request $request)
    {
        // \Log::notice('update_expenses');
        // \Log::notice($request->all());

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'expense_id' => 'required|numeric|exists:expenses,id',
            'expense_item_id' => 'required|numeric|exists:expenses_items,id',

            "expenses_lines_id"  => 'required|numeric|exists:expenseslines,id',
            "currency_id"  => 'required|numeric|exists:currencies,id',
            "expenses_amount"  => 'required|digits_between:1,8',
            "date"  => 'required|date',
            "description"   => "nullable",
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        // where('id', $request->expense_id)->value('');
        $old_expnese_lines_name = \DB::table('expenseslines')->where('id', $request->expenses_lines_id)->value('expenseslines_name');

        $desc = blank( $request->description ) ? '' : $request->description;

        ExpenesItemModel::where('expenses_id', $request->expense_id)
            ->where('id', $request->expense_item_id)
            ->update([
                'expenses_lines_id' => $request->expenses_lines_id,
                'currency_id' => $request->currency_id,
                'cost' => $request->expenses_amount,
                'date' => $request->date,
                'description' => $desc,
            ]);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('expense','edit',$request->expense_id,$request->user()->id,$request->user()->pm_company_id,$old_expnese_lines_name, 'expense_edit');

        // return $this->sendResponse([], 'Item updated successfully.', 200, 200);
        return $this->sendResponse([], __(app()->getLocale() . '.item_updated'), 200, 200);
    }


    //POST
    public function delete_expense_item_image(Request $request)
    {
        $validator = validator($request->all(), [
            'image_id' => 'required|numeric|exists:expense_files,id',
            'item_id' => 'required|numeric|exists:expenses_items,id'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $files_data = \DB::table('expense_files')
            ->where('expense_item_id', $request->item_id)
            ->where('id', $request->image_id)
            ->select('file_name')
            ->first();
        if (!blank($files_data)) {
            \App\Services\FileUploadService::delete_contract_document_from_azure($files_data->file_name, 'delete_expense_item_image');
        }

        \DB::table('expense_files')
            ->where('id', $request->image_id)
            ->where('expense_item_id', $request->item_id)
            ->delete();

        // return $this->sendResponse([], 'Document deleted successfully', 200, 200);
        return $this->sendResponse([], __(app()->getLocale() . '.document_deleted'), 200, 200);
    }


    //POST
    public function delete_expense_item_by_id(Request $request)
    {
        $validator = validator($request->all(), [
            'item_id' => 'required|numeric|exists:expenses_items,id'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $files_data = \DB::table('expense_files')->where('expense_item_id', $request->item_id)->select('file_name')->get();

        $old_expense_item = \DB::table('expenses_items')->where('id', $request->item_id)->value('cost');

        //module,action,affected_record_id,pm_id,pm_company_id,record_name
        \App\Services\PmLogService::pm_log_delete_entry('expense','delete',$request->item_id,$request->user()->id,$request->user()->pm_company_id,$old_expense_item,'expense_deleted');

        \DB::table('expenses_items')->where('id', $request->item_id)->delete();

        foreach ($files_data as $value) {
            \App\Services\FileUploadService::delete_contract_document_from_azure($value->file_name, 'delete_expense_item_by_id');
        }

        // return $this->sendResponse([], 'Item deleted successfully', 200, 200);
        return $this->sendResponse([], __(app()->getLocale() . '.item_deleted'), 200, 200);
    }


    public function add_image_to_old_item(Request $request)
    {
        $validator = validator($request->all(), [
            'item_id' => 'required|numeric|exists:expenses_items,id',
            'item_single_file' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $file      = $request->file('item_single_file');

        $filename =  uniqid() . '.' . $file->getClientOriginalExtension();

        try {
            \Storage::disk('azure_documents')->put($filename, \File::get($file));

            $expenses_items_id = ExpenesItemFilesModel::create([
                'file_name' => $filename,
                'expense_item_id'  => $request->item_id
            ]);
        } catch (\Exception $e) {
            \Log::error('---------add_expense---------- ' . $e);
        }

        // return $this->sendResponse([], 'Document uploaded successfully', 200, 200);
        return $this->sendResponse([], __(app()->getLocale() . '.document_uploaded'), 200, 200);
    }



    //POST
    public function tenant_notification_list_for_pm(Request $request)
    {
        $lang = app()->getLocale();

        $validator = validator($request->all(), [
            'page' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $tenant_notification_list = TenantNotificationModel::leftJoin('tenants', 'tenant_notifications.tenant_id', '=', 'tenants.id')
            ->where('tenant_notifications.pm_company_id', $request->user()->pm_company_id)
            // ->where('tenant_notifications.message_language', $lang)
            ->select(
                'tenant_notifications.id',
                'tenant_notifications.title',
                'tenant_notifications.message',
                'tenant_notifications.created_at',
                'tenants.first_name',
                'tenants.last_name',
            )
            ->take(10)
            ->skip($skip)
            ->orderBy('id', 'DESC')
            ->get()
            ->transform(function ($row) {

                $row->send_to = blank($row->first_name) ? 'All' : $row->first_name . ' ' . $row->last_name;

                $row->message =  mb_strimwidth($row->message, 0, 15, '...');

                $row->title =  mb_strimwidth($row->title, 0, 15, '...');

                $row->date = date('d-M-Y h:i A', strtotime($row->created_at));

                unset($row->created_at);

                return $row;
            });


        $tenant_notification_list_count = TenantNotificationModel::leftJoin('tenants', 'tenant_notifications.tenant_id', '=', 'tenants.id')
            ->where('tenant_notifications.pm_company_id', $request->user()->pm_company_id)
            ->where('tenant_notifications.message_language', $lang)
            ->count();

        $response = [
            'success' => true,
            'data'    => $tenant_notification_list,
            'message' => 'tenant_notification_list',
            'pagecount'  => (int)ceil($tenant_notification_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    // PM view noti list from admin
    public function admin_notification_list(Request $request)
    {
        $lang = app()->getLocale();

        $validator = validator($request->all(), [
            'page' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        $admin_notification_list = PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
            // ->where('message_language', $lang)
            ->select(
                'id',
                'title',
                'message',
                'created_at',
            )
            ->take(10)
            ->skip($skip)
            ->orderBy('id', 'DESC')
            ->get()
            ->transform(function ($row) use($request) {

                $row->message =  mb_strimwidth($row->message, 0, 15, '...');

                $row->title =  mb_strimwidth($row->title, 0, 15, '...');

                $row->date = date('d-M-Y h:i A', strtotime($row->created_at));

                unset($row->created_at);

                return $row;
            });


        $admin_notification_list_count = PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
            ->where('message_language', $lang)
            ->count();

        $response = [
            'success' => true,
            'data'    => $admin_notification_list,
            'message' => 'admin_notification_list',
            'pagecount'  => (int)ceil($admin_notification_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }
}
