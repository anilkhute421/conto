<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\BuildingModel;
use Illuminate\Http\Request;

class NotificationController extends ApiBaseController
{

    //for PM header
    // GET
    public function admin_to_pm_notification_count(Request $request)
    {
        // \Log::notice('pm id - '. $request->user()->id);

        $lang = app()->getLocale();

        // code same as common_count_of_admin_notification_for_pm function
        $count = \App\Models\PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
            ->whereRaw('NOT FIND_IN_SET(?,seen_by)', [$request->user()->id])
            ->where('message_language', $lang)
            ->count();

        $sum = \App\Models\MaintanceRequestModel::where('pm_company_id', $request->user()->pm_company_id)
               ->sum('tenant_unread_count');

        $response = [
            'success' => true,
            // 'count'    => $this->common_count_of_admin_notification_for_pm($request,$lang),
            'count' => $count,
            'message' => $count,
            'sum' => $sum,
            'status' => 200,

        ];
        return response()->json($response, 200);
    }

    public function common_count_of_admin_notification_for_pm($request, $lang)
    {

        return \App\Models\PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
            ->whereRaw('NOT FIND_IN_SET(?,seen_by)', [$request->user()->id])
            ->where('message_language', $lang)
            ->count();
    }

    // GET
    public function tenant_drop_down_at_notification(Request $request)
    {

        // $all = new \stdClass();
        // $all->id = 0;
        // $all->name = 'All';

        // $collection = collect([0 =>$all]);

        $_temp_listing = \DB::table('tenants')->where('pm_company_id', $request->user()->pm_company_id)
            ->select('first_name', 'last_name', 'id')
            ->get()
            ->transform(function ($row) {
                $row->name = $row->first_name . ' ' . $row->last_name;
                unset($row->first_name);
                unset($row->last_name);

                return $row;
            });

        // $merged = $collection->merge($_temp_listing);

        // $listing = $merged->all();
        $listing = $_temp_listing;

        return $this->sendResponse($listing, 'tenant_drop_down_at_notification', 200, 200);
    }

    //POST
    public function pm_send_notification_to_tenant(Request $request)
    {
        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), [
            'type' => 'required|in:tenant,building',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ($request->type == 'tenant') {

            $validator = validator($request->all(), [
                'title' => 'required|max:50',
                'message' => 'required|max:500',
                'message_language' => 'required|in:en,ar',
                'tenant_ids' => 'required|array',

            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            foreach ($request->tenant_ids as $tenant_id) {
                if ($tenant_id == 0) {

                    \DB::table('tenant_notifications')->insert([
                        'title' => $request->title,
                        'message' => $request->message,
                        'tenant_id' => 0, //all
                        'pm_company_id' => $request->user()->pm_company_id,
                        'property_manager_id' => $request->user()->id,
                        'message_language' => $request->message_language,
                        'seen_by' => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    //start
<<<<<<< HEAD
                    $type = 'noti';
                    \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_admin_topic', $request->title, $request->message, 'pm_all_tenant_push_notofication',$type);
=======
                    \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_admin_topic'.'_'.$request->message_language, $request->title, $request->message, 'pm_all_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316
                    break;
                } else {
                    //validate tenant_id
                    $validator = validator(['tenant_id' => $tenant_id], [
                        'tenant_id' => 'required|numeric|exists:tenants,id',
                    ]);

                    if ($validator->fails()) {
                        return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
                    }

                    \DB::table('tenant_notifications')->insert([
                        'title' => $request->title,
                        'message' => $request->message,
                        'tenant_id' => $tenant_id,
                        'pm_company_id' => $request->user()->pm_company_id,
                        'property_manager_id' => $request->user()->id,
                        'message_language' => $request->message_language,
                        'seen_by' => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    //start
<<<<<<< HEAD
                    $type = 'noti';
                    \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id, $request->title, $request->message, 'pm_all_tenant_push_notofication',$type);
=======
                    \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id.'_'.$request->message_language, $request->title, $request->message, 'pm_all_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316
                }
            } //foreach
        } elseif ($request->type == 'building') {

            $validator = validator($request->all(), [
                'title' => 'required|max:50',
                'message' => 'required|max:500',
                'message_language' => 'required|in:en,ar',
                'building_ids' => 'required|array',
                // "building_ids.*"  => 'required|numeric|exists:buildings,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }

            $final_tenant_id = [];
            // $i = 0;
            foreach ($request->building_ids as $building_id) {

                if ($building_id == 0) {

                    //get all buildings ids.
                    $Buildings_ids = BuildingModel::where('pm_company_id', $request->user()->pm_company_id)
                        ->select('id', 'building_name')->get();

                    foreach ($Buildings_ids as $Buildings_id) {

                        $tenant_ids = \DB::table('tenants_units')->where('building_id', $Buildings_id->id)->where('tenant_id', '!=', 0)->select('tenant_id')->get();

                        foreach ($tenant_ids as $tenant_id) {

                            if (!in_array($tenant_id, $final_tenant_id)) {
                                array_push($final_tenant_id, $tenant_id);
                                //save send

                                \DB::table('tenant_notifications')->insert([
                                    'title' => $request->title,
                                    'message' => $request->message,
                                    'tenant_id' => $tenant_id->tenant_id,
                                    'pm_company_id' => $request->user()->pm_company_id,
                                    'property_manager_id' => $request->user()->id,
                                    'message_language' => $request->message_language,
                                    'seen_by' => '',
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]);
<<<<<<< HEAD
                                 
                                $type = 'noti';
                                \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id->tenant_id, $request->title, $request->message, 'pm_tenant_push_notofication',$type);
=======
                                \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id->tenant_id.'_'.$request->message_language, $request->title, $request->message, 'pm_single_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316
                            }
                        }
                    }
                    break;
                } else {
                    //validate tenant_id
                    $validator = validator(['building_id' => $building_id], [
                        'building_id' => 'required|numeric|exists:buildings,id',
                    ]);

                    if ($validator->fails()) {
                        return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
                    }

                    $tenant_ids = \DB::table('tenants_units')->where('building_id', $building_id)->where('tenant_id', '!=', 0)->select('tenant_id')->get();

                    //check in final_tenent_id exist.
                    foreach ($tenant_ids as $tenant_id) {

                        if (!in_array($tenant_id, $final_tenant_id)) {
                            array_push($final_tenant_id, $tenant_id);
                            //save send

                            \DB::table('tenant_notifications')->insert([
                                'title' => $request->title,
                                'message' => $request->message,
                                'tenant_id' => $tenant_id->tenant_id,
                                'pm_company_id' => $request->user()->pm_company_id,
                                'property_manager_id' => $request->user()->id,
                                'message_language' => $request->message_language,
                                'seen_by' => '',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
<<<<<<< HEAD

                            $type = 'noti';

                            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id->tenant_id, $request->title, $request->message, 'pm_tenant_push_notofication',$type);
=======
                            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_' . $tenant_id->tenant_id.'_'.$request->message_language, $request->title, $request->message, 'pm_single_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316
                        }
                    }
                }
            } //endforeach
        }
        return $this->sendResponse([], __(app()->getLocale() . '.noti_sent_success'), 200, 200);
    }

    //POST
    public function view_tenant_notification_for_pm(Request $request)
    {
        $validator = validator($request->all(), [
            'tenant_notification_id' => 'required|numeric|exists:tenant_notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $detail = \DB::table('tenant_notifications')->where('id', $request->tenant_notification_id)
            ->select('title', 'message', 'tenant_id', 'message_language', 'created_at')
            ->first();

        $detail->created_at = date('d-M-Y h:i A', strtotime($detail->created_at));

        if ($detail->tenant_id != 0) {
            $tenant = \DB::table('tenants')
                ->where('id', $detail->tenant_id)
                ->select('first_name', 'last_name')
                ->first();
            $detail->tenant = $tenant->first_name . ' ' . $tenant->last_name;
        } else {
            $detail->tenant = 'All';
        }

        return $this->sendResponse($detail, 'view_tenant_notification_for_pm', 200, 200);
    }
}
