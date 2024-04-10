<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiBaseController;
use App\Models\CommentMediaModel;
use App\Models\MaintanceRequestModel;
use App\Models\TenantNotificationModel;
use App\Models\MaintenanceFilesModel;
use App\Models\TenantModel;
use App\Http\Requests\MobileRequest;



class ATenantThreeController extends ApiBaseController
{
    //POST(right)
    public function delete_attachment_by_maintenance_request_id(Request $request)
    {
        try{
    		\App::setLocale($_SERVER['HTTP_LANG']);
         }catch(\Exception $e){
            return $this->sendSingleFieldError('Sorry, language is required in header',201,200);
         }
         
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

            \DB::table('maintenance_files')
                ->where('maintenance_request_id', $request->maintenance_request_id)
                ->where('id', $request->maintenance_file_id)
                ->delete();
        }


        $MaintenanceFiles = \DB::table('maintenance_files')->where('maintenance_request_id', $request->maintenance_request_id)
            ->select('file_name', 'file_type', 'thumbnail_name', 'id')
            ->get();

        $response = [
            'success' => true,
            'data'    => $MaintenanceFiles,
            'message' => __(app()->getLocale().'.attachment_deleted_successfully'),
            'status'  => 200
        ];
        return response()->json($response, 200);

    }


    //POST
    public function upload_comment_media_by_maintenance_request_id(Request $request)
    {
        // \Log::info('upload_comment_media');
        // \Log::info($request->all());

        // ini_set('upload_max_filesize' , '900M');
        // ini_set('post_max_size', '900M');
        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), MobileRequest::upload_comment_media_by_maintenance_request_id());
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

            // return $this->sendResponse(['media_url' => $image_full_url, 'thumbnail_url' => \Config::get('constants.image_base_url') . '/' .  $thumb_name], 'media_upload_successfully.', 200, 200);
            return $this->sendResponse(['media_url' => $image_full_url, 'thumbnail_url' => \Config::get('constants.image_base_url') . '/' .  $thumb_name], __(app()->getLocale().'.media_upload_successfully'), 200, 200);

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

            // return $this->sendResponse(['media_url' => $video_full_url, 'thumbnail_url' => $video_thumbnail_full_url], 'Media uploaded successfully.', 200, 200);
            return $this->sendResponse(['media_url' => $video_full_url, 'thumbnail_url' => $video_thumbnail_full_url], __(app()->getLocale().'.media_upload_successfully'), 200, 200);

        }
    }

    //POST
    public function tenant_notification_list(Request $request)
    {
        try{
    		\App::setLocale($_SERVER['HTTP_LANG']);
         }catch(\Exception $e){
            return $this->sendSingleFieldError('Sorry, language is required in header',201,200);
         }

        $lang = app()->getLocale();

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            // 'tenant_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;


        $notification_details = TenantNotificationModel::where('tenant_notifications.message_language', $lang)
            ->whereIn('tenant_notifications.tenant_id', [0,$request->user()->id])
            ->select('id','title','message','created_at')
            // ->take(10)
            // ->skip($skip)
            ->orderBy('id','DESC')
            ->get()
            ->transform(function ($row) {
                $row->message =  mb_strimwidth( $row->message, 0, 15, '...');
                $row->date = date('d-M-y (h:i A)', strtotime($row->created_at));
                unset($row->created_at);
                return $row;
            });

      $notification_details_count = TenantNotificationModel::where('tenant_notifications.message_language', $lang)
        ->whereIn('tenant_notifications.tenant_id', [0,$request->tenant_id])
        ->count();

        $response = [
            'success' => true,
            'data'    => $notification_details,
            'message' => 'notification_list_by_tenant_id',
            'pagecount'  => (int)ceil($notification_details_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //POST
    public function view_tenant_notification_list(Request $request)
    {
        try{
    		\App::setLocale($_SERVER['HTTP_LANG']);
         }catch(\Exception $e){
            return $this->sendSingleFieldError('Sorry, language is required in header',201,200);
         }

        $lang = app()->getLocale();

        $validator = validator($request->all(), [
            'tenant_notifications_id' => 'required|numeric|exists:tenant_notifications,id',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }
     
        $view_notification_details = TenantNotificationModel::where('tenant_notifications.id', $request->tenant_notifications_id)
            ->select('id','title','message','created_at', 'message_language', 'seen_by', 'tenant_id')
            ->first();

        $view_notification_details->date = date('d-M-y (h:i A)', strtotime($view_notification_details->created_at));
            
        unset($view_notification_details->created_at);

         //check if seen or not
         $tenant_seen = \DB::table('tenant_notifications')
         ->where('id', $request->tenant_notifications_id)
         ->whereRaw('FIND_IN_SET(?,seen_by)', [$request->user()->id])
         ->select('id')
         ->first();

         $has_seen = blank($tenant_seen) ? 0 : 1;

         if($has_seen == 0){
            //update in db
            //if send to - all PM
            if($view_notification_details->tenant_id == 0){

                if(!blank($view_notification_details->seen_by)){
                    //a if other seen, then merge and update
                    try{

                        \DB::table('tenant_notifications')->where('id', $request->tenant_notifications_id)
                        ->update([
                            'seen_by' => $view_notification_details->seen_by.$request->user()->id.',',
                        ]);
                    }catch (\Exception $e){
                        Log::error('----view_admin_notification_for_pm----- '.json_encode($e) );
                    }

                }elseif(blank($view_notification_details->seen_by)){
                    //b if seen_by empty, then update

                    \DB::table('tenant_notifications')->where('id', $request->tenant_notifications_id)
                        ->update([
                            'seen_by' => $request->user()->id.',',
                        ]);
                }

            }elseif($view_notification_details->tenant_id == $request->user()->id){
                //if logged in PM only then update
                \DB::table('tenant_notifications')->where('id', $request->tenant_notifications_id)
                    ->update([
                        'seen_by' => $request->user()->id.',',
                    ]);
            }

        }

        $response = [
            'success' => true,
            'data'    => $view_notification_details,
            'count'    => app('App\Http\Controllers\TenantController')->common_count_of_tenant_notification($request,$lang),
            'message' => 'view_tenant_notification_list',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //POST
    public function tenant_logout(Request $request)
    {
        
        $user = TenantModel::find($request->user()->id);

        $user->tokens()->delete();

        $response = [
            'success' => true,
            'data'    => [],
            // 'message' => 'Logout successful',
            'message' => __(app()->getLocale().'.logout'),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }
}
