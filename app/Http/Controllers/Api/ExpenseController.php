<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenesModel;
use App\Models\MaintenanceFilesModel;
use App\Models\CommentMediaModel;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\PmRequest;
use App\Models\MaintanceRequestModel;
use Log;


class ExpenseController extends ApiBaseController
{

    public function search_maintanence_expenses(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
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

        if ( ($request->expense_line_id != 0) ) {
            $validator = validator($request->all(), [
                'expense_line_id' => 'exists:expenseslines,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        \DB::statement("SET SQL_MODE=''");
        $search_maintanence_expenses = \DB::table('expenses_items')
            ->leftJoin('expenses', 'expenses_items.expenses_id', '=', 'expenses.id')
            ->leftJoin('maintance_requests', 'expenses.request_id', '=', 'maintance_requests.id')
            ->leftJoin('currencies', 'expenses_items.currency_id', '=', 'currencies.id')
            ->leftJoin('expenseslines', 'expenses_items.expenses_lines_id', '=', 'expenseslines.id')
            ->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

            ->where('expenses.pm_company_id', $request->user()->pm_company_id);

        $search_maintanence_expenses->where(function ($query) use ($search_key) {

            $query->where('expenses_items.cost', 'LIKE', "%$search_key%")
                ->orWhere('maintance_requests.request_code', 'LIKE', "%$search_key%")
                ->orWhere('expenseslines.expenseslines_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");
        });

        if($request->expense_line_id != 0){
            $search_maintanence_expenses->where('expenseslines.id', $request->expense_line_id);
        }

        //amount between--------------------------
        if(!blank($request->amount_from) && !blank($request->amount_to)){
            $startCost = $request->amount_from;
            $endCost = $request->amount_to;
            if($startCost == $endCost){
                $search_maintanence_expenses->where('expenses_items.cost', '=', $endCost);
            }else{
                $search_maintanence_expenses->whereBetween('expenses_items.cost', [$startCost, $endCost]);
            }
        }elseif(!blank($request->amount_from)){
            $startCost = $request->amount_from;
            $search_maintanence_expenses->where('expenses_items.cost', '>=', $startCost);
        }elseif(!blank($request->amount_to)){
            $endCost = $request->amount_to;
            $search_maintanence_expenses->where('expenses_items.cost', '<=', $endCost);
        }

        //date between-----------------------------
        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_maintanence_expenses->whereDate('expenses_items.date', '=', $endDate);
            }else{
                $search_maintanence_expenses->whereBetween('expenses_items.date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_maintanence_expenses->whereDate('expenses_items.date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_maintanence_expenses->whereDate('expenses_items.date', '<=', $endDate);
        }

        $search_maintanence_expenses->select(
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

        $all_data = $search_maintanence_expenses->groupBy('expenses_items.id')->get()
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



        $search_maintanence_expenses_count =  \DB::table('expenses_items')
        ->leftJoin('expenses', 'expenses_items.expenses_id', '=', 'expenses.id')
        ->leftJoin('maintance_requests', 'expenses.request_id', '=', 'maintance_requests.id')
        ->leftJoin('currencies', 'expenses_items.currency_id', '=', 'currencies.id')
        ->leftJoin('expenseslines', 'expenses_items.expenses_lines_id', '=', 'expenseslines.id')
        ->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
        ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

        ->where('expenses.pm_company_id', $request->user()->pm_company_id);

        $search_maintanence_expenses_count->where(function ($query) use ($search_key) {

            $query->where('expenses_items.cost', 'LIKE', "%$search_key%")
                ->orWhere('maintance_requests.request_code', 'LIKE', "%$search_key%")
                ->orWhere('expenseslines.expenseslines_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants_units.unit_no', 'LIKE', "%$search_key%")
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%");
        });

        if($request->expense_line_id != 0){
            $search_maintanence_expenses_count->where('expenseslines.id', $request->expense_line_id);
        }



        //amount between--------------------------
        if(!blank($request->amount_from) && !blank($request->amount_to)){
            $startCost = $request->amount_from;
            $endCost = $request->amount_to;
            if($startCost == $endCost){
                $search_maintanence_expenses_count->where('expenses_items.cost', '=', $endCost);
            }else{
                $search_maintanence_expenses_count->whereBetween('expenses_items.cost', [$startCost, $endCost]);
            }
        }elseif(!blank($request->amount_from)){
            $startCost = $request->amount_from;
            $search_maintanence_expenses_count->where('expenses_items.cost', '>=', $startCost);
        }elseif(!blank($request->amount_to)){
            $endCost = $request->amount_to;
            $search_maintanence_expenses_count->where('expenses_items.cost', '<=', $endCost);
        }

        //date between-----------------------------
        if(!blank($request->date_from) && !blank($request->date_to)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            if($startDate == $endDate){
                $search_maintanence_expenses_count->whereDate('expenses_items.date', '=', $endDate);
            }else{
                $search_maintanence_expenses_count->whereBetween('expenses_items.date', [$startDate, $endDate]);
            }
        }elseif(!blank($request->date_from)){
            $startDate = \Illuminate\Support\Carbon::create( $request->date_from);
            $search_maintanence_expenses_count->whereDate('expenses_items.date', '>=', $startDate);
        }elseif(!blank($request->date_to)){
            $endDate = \Illuminate\Support\Carbon::create( $request->date_to);
            $search_maintanence_expenses_count->whereDate('expenses_items.date', '<=', $endDate);
        }

        $count = $search_maintanence_expenses_count
        ->distinct('expenses_items.id')
        ->count();


        $response = [
            'success' => true,
            'data'    => $all_data,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }



    public function upload_maintenance_files(Request $request)
    {
        // \Log::info('upload_maintenance_files');
        // \Log::info($request->all());

        // ini_set('upload_max_filesize' , '900M');
        // ini_set('post_max_size', '900M');
        ini_set('max_execution_time', 0);
        // ini_set('memory_limit', '500M');

        // \Log::info('upload_max_filesize '.ini_get("upload_max_filesize"));
        // \Log::info('post_max_size '.ini_get("post_max_size"));
        // \Log::info('max_execution_time '.ini_get("max_execution_time"));


        $validator = validator($request->all(), PmRequest::upload_maintenance_files());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //upload image.
        if ($request->file_type == 1) {

            // $file     = $request->file('attachment');
            // $filename =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

            try {
                // \Storage::disk('azure')->put($filename, \File::get($file));

                $maintenace_files = MaintenanceFilesModel::create([
                    'maintenance_request_id' => $request->maintenance_request_id,
                    'file_type' => $request->file_type,
                    'file_name' => $request->attachment,
                    'thumbnail_name' => $request->thumbnail_name,
                    'upload_by' => $request->user()->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('---------upload_maintenance_files image---------- ' . $e);
            }

            // $image_full_url = \Config::get('constants.image_base_url') . '/' . $filename;
            // \Log::info('---------$image_full_url image---------- ' . $image_full_url);

            // try {
                // $thumb_name =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

                // $destinationPath = public_path('/thumbnail');

                // $img = \Intervention\Image\Facades\Image::make($image_full_url);

                // $img->resize(200, 120, function ($constraint) {
                //     $constraint->aspectRatio();
                // })
                //     ->save($destinationPath . '/' . $thumb_name);

                // $resource = fopen($destinationPath . '/' . $thumb_name, 'r+');

                // \Storage::disk('azure')->put($thumb_name,  $resource);

                //delete from public
                // unlink($destinationPath . '/' . $thumb_name);

                // \Log::info('---------thumb_name full_url---------- ' . \Config::get('constants.image_base_url') . '/' .  $thumb_name);

                // MaintenanceFilesModel::where('id', $maintenace_files->id)->update(['thumbnail_name' => $thumb_name]);
            // } catch (\Exception $e) {
                // \Log::error('---------upload_maintenance_files image thumb---------- ' . $e);
            // }
        }
        //upload video.
        if ($request->file_type == 2) {

            // $validator = validator($request->all(), ['thumbnail' => 'required']);
            // if ($validator->fails()) {
            //     return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            // }

            // $file   = $request->file('attachment');

            // $filename =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

            // try {
                // $resource = fopen($file->getRealPath(), 'r+');

                // \Storage::disk('azure_videos')->put($filename, $resource);

                // \Log::info('---------upload_maintenance_files video full_url---------- ' . \Config::get('constants.video_base_url') . '/' .  $filename);
            // } catch (\Exception $e) {

                // \Log::error('------------------ "upload_maintenance_files video " ------------------ ' . $e);
            // }

            //thumbnail_for_video
            // $thumbnail_video = $request->file('thumbnail');

            // $thumbnail_name =  $request->maintenance_request_id . uniqid() . '.' . $thumbnail_video->getClientOriginalExtension();

            // try {
            //     $resource = fopen($thumbnail_video->getRealPath(), 'r+');

            //     \Storage::disk('azure')->put($thumbnail_name, $resource);

            //     \Log::info('---------upload_maintenance_files video_thumb full_url---------- ' . \Config::get('constants.image_base_url') . '/' .  $thumbnail_name);
            // } catch (\Exception $e) {
            //     Log::error('------------------ "upload_maintenance_files video" ------------------ ' . $e);
            // }

            $maintenace_files = MaintenanceFilesModel::create([
                'maintenance_request_id' => $request->maintenance_request_id,
                'file_type' => $request->file_type,
                'file_name' => $request->attachment,
                'thumbnail_name' => $request->thumbnail_name,
                // 'thumbnail_name' => '',
                'upload_by' => $request->user()->id,
            ]);
        }
        return $this->sendResponse([],__(app()->getLocale().'.file_upload_successfully'), 200, 200);
    }


    public function upload_comment_media(Request $request)
    {
        // \Log::info('upload_comment_media');
        // \Log::info($request->all());

        // ini_set('upload_max_filesize' , '900M');
        // ini_set('post_max_size', '900M');
        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), PmRequest::upload_comment_media());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //upload image.
        if ($request->media_type == 1) {

            $file     = $request->file('attachment');
            $filename =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

            try {
                \Storage::disk('azure')->put($filename, \File::get($file));

                $maintenace_files = CommentMediaModel::create([
                    'maintenance_request_id' => $request->maintenance_request_id,
                    'media_type' => $request->media_type,
                    'media_name' => $filename,
                    'thumbnail_name' => '',
                    'upload_by' => $request->user()->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('---------upload_comment_media image---------- ' . $e);
            }

            $image_full_url = \Config::get('constants.image_base_url') . '/' . $filename;
            // \Log::info('---------upload_comment_media $image_full_url image---------- ' . $image_full_url);

            try {
                $thumb_name =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

                $destinationPath = public_path('/thumbnail');

                $img = \Intervention\Image\Facades\Image::make($image_full_url);

                $img->resize(200, 120, function ($constraint) {
                    $constraint->aspectRatio();
                })
                    ->save($destinationPath . '/' . $thumb_name);

                $resource = fopen($destinationPath . '/' . $thumb_name, 'r+');

                \Storage::disk('azure')->put($thumb_name,  $resource);

                //delete from public
                unlink($destinationPath . '/' . $thumb_name);

                // \Log::info('---------upload_comment_media thumb_name full_url---------- ' . \Config::get('constants.image_base_url') . '/' .  $thumb_name);

                CommentMediaModel::where('id', $maintenace_files->id)->update(['thumbnail_name' => $thumb_name]);
            } catch (\Exception $e) {
                \Log::error('---------upload_comment_media image thumb---------- ' . $e);
            }

            return $this->sendResponse(['media_url' => $image_full_url, 'thumbnail_url' => \Config::get('constants.image_base_url') . '/' .  $thumb_name],__(app()->getLocale().'.media_upload_successfully'), 200, 200);
        }

        //upload video.
        if ($request->media_type == 2) {

            // $validator = validator($request->all(), ['thumbnail' => 'required']);
            // if ($validator->fails()) {
            //     return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            // }

            $file   = $request->file('attachment');

            $filename =  $request->maintenance_request_id . uniqid() . '.' . $file->getClientOriginalExtension();

            try {
                $resource = fopen($file->getRealPath(), 'r+');

                \Storage::disk('azure_videos')->put($filename, $resource);

                // \Log::info('---------upload_comment_media video full_url---------- ' . \Config::get('constants.video_base_url') . '/' .  $filename);
            } catch (\Exception $e) {

                \Log::error('------------------ "upload_comment_media video " ------------------ ' . $e);
            }

            //thumbnail_for_video
            // $thumbnail_video = $request->file('thumbnail');

            // $thumbnail_name =  $request->maintenance_request_id . uniqid() . '.' . $thumbnail_video->getClientOriginalExtension();

            // try {
                // $resource = fopen($thumbnail_video->getRealPath(), 'r+');

                // \Storage::disk('azure')->put($thumbnail_name, $resource);

                // \Log::info('---------upload_comment_media video_thumb full_url---------- ' . \Config::get('constants.image_base_url') . '/' .  $thumbnail_name);
            // } catch (\Exception $e) {
            //     \Log::error('------------------ "upload_comment_media video" ------------------ ' . $e);
            // }

            $maintenace_files = CommentMediaModel::create([
                'maintenance_request_id' => $request->maintenance_request_id,
                'media_type' => $request->media_type,
                'media_name' => $filename,
                // 'thumbnail_name' => $thumbnail_name,
                'thumbnail_name' => '',
                'upload_by' => $request->user()->id,
            ]);

            $video_full_url = \Config::get('constants.video_base_url') . '/' .  $filename;
            // $video_thumbnail_full_url = \Config::get('constants.image_base_url') . '/' .  $thumbnail_name;
            $video_thumbnail_full_url = \Config::get('constants.video_thumb_url');

            return $this->sendResponse(['media_url' => $video_full_url, 'thumbnail_url' => $video_thumbnail_full_url],__(app()->getLocale().'.media_upload_successfully'), 200, 200);
        }
    }

    //post
    public function fetch_maintanence_request_all_attachment(Request $request)
    {
        $validator = validator($request->all(), [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        //get data from MaintenanceFilesModel
        // $MaintenanceFiles = MaintenanceFilesModel::where('maintenance_request_id', $request->maintenance_request_id)
        //     ->select('file_name', 'file_type', 'thumbnail_name', 'id')
        //     ->get();

        $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
            ->select('file_name', 'file_type', 'thumbnail_name', 'id')
            ->get();


        //get data from CommentMediaModel
        // $CommentMedia = CommentMediaModel::where('maintenance_request_id', $request->maintenance_request_id)
        //     ->select('media_name', 'thumbnail_name', 'id', 'media_type')
        //     ->get();


        $response = [
            'success' => true,
            'MaintenanceFiles'    => $MaintenanceFiles,
            // 'CommentMedia'    => $CommentMedia,
            'message' => 'fetch_maintanence_request_all_attachment',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //POST(right)
    public function delete_attachment_by_id(Request $request)
    {
        $validator = validator($request->all(), [
            'maintenance_request_id' => 'required|numeric|exists:maintance_requests,id',
            'maintenance_file_id' => 'required|numeric|exists:maintenance_files,id'
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $files_data = \DB::table('maintenance_files')
            ->where('id', $request->maintenance_file_id)
            ->where('maintenance_request_id', $request->maintenance_request_id)
            ->select('file_name', 'thumbnail_name')
            ->first();

        if (!blank($files_data)) {
            \App\Services\FileUploadService::delete_attachment_by_id_from_azure($files_data->file_name, 'delete_attachment_by_id file');

            \App\Services\FileUploadService::delete_attachment_by_id_from_azure($files_data->thumbnail_name, 'delete_attachment_by_id thumb');

            \DB::table('maintenance_files')
                ->where('maintenance_request_id', $request->maintenance_request_id)
                ->where('id', $request->maintenance_file_id)
                ->delete();
        }

        // return $this->sendResponse([], 'Attachment deleted successfully', 200, 200);
        return $this->sendResponse([],__(app()->getLocale().'.attachment_deleted_successfully'), 200, 200);

        
    }


    //post
    //for expert(without_token)
    public function maintenance_request_details_for_expets(Request $request)
    {
        $validator = validator($request->all(), [
            'unique_code' => 'required|exists:maintenance_experts,unique_code',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $maintenance_id = \DB::table('maintenance_experts')->where('unique_code', $request->unique_code)->value('maintenance_id');

        if(!blank($maintenance_id)){

            //get data from MaintenanceFilesModel
            $MaintenanceFiles = MaintenanceFilesModel::where('maintenance_request_id', $maintenance_id)
                ->select('file_name', 'file_type', 'thumbnail_name', 'id')
                ->get();

            //maintenance_request_details.
            $maintenance_details = (object)[];

            // $maintenance_details = MaintanceRequestModel::where('id', $maintenance_id)
            //     ->select('request_code', 'maintenance_request_id', 'id', 'building_id', 'unit_id', 'description', 'tenant_id', 'status')
            //     ->first();

            // // get data from bulidings.
            // $_temp_data = \DB::table('buildings')->where('id', $maintenance_details->building_id)->select('building_name', 'address')->first();

            // //building_name
            // $maintenance_details->building_name =  $_temp_data->building_name;

            // //building_address
            // $maintenance_details->building_address =  $_temp_data->address;

            // //get data from maintenance
            // $_temp_data = \DB::table('maitinance_request_for')->where('id', $maintenance_details->maintenance_request_id)->select('maitinance_request_name', 'id')->first();

            // $maintenance_details->request_for = $_temp_data->maitinance_request_name;

            // $maintenance_details->request_for_id = $_temp_data->id;

            // //get data from tenant_unit
            // $maintenance_details->unit_no = \DB::table('tenants_units')->where('id', $maintenance_details->unit_id)->value('unit_no');

            // if ($maintenance_details->tenant_id != 0) {
            //     //get tenant details
            //     $tenant_details = \DB::table('tenants')->where('id', $maintenance_details->tenant_id)->select('last_name', 'first_name')->first();

            //     $maintenance_details->requested_by = !blank($tenant_details) ? $tenant_details->first_name . ' ' . $tenant_details->last_name : '';
            // } else {
            //     $maintenance_details->requested_by = '';
            // }

            // switch ($maintenance_details->status) {
            //     case 1:
            //         $status_string = ' Request Raised';
            //         break;
            //     case 2:
            //         $status_string = 'Request Assigned';
            //         break;
            //     case 3:
            //         $status_string = 'Request completed';
            //         break;
            //     case 4:
            //         $status_string = 'Request is on hold';
            //         break;
            //     case 5:
            //         $status_string = 'Request canceled';
            //         break;
            // }

            // $maintenance_details->status_int =  $maintenance_details->status;

            // $maintenance_details->status =  $status_string;

            $response = [
                'success' => true,
                'MaintenanceFiles'    => $MaintenanceFiles,
                'maintenance_details'    => $maintenance_details,
                'message' => 'maintenance_request_detaills_for_expets',
                'status'  => 200
            ];
            return response()->json($response, 200);

        }
        else{
            $response = [
                'success' => false,
                // 'message' => 'Sorry link is expired',
                'message' => __(app()->getLocale().'.link_expired'),
                'status'  => 201
            ];
            return response()->json($response, 201);

        }
            }
}
