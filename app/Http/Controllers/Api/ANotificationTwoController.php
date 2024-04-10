<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiBaseController;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

class ANotificationTwoController extends ApiBaseController
{

    //POST
    public function view_admin_notification_for_pm(Request $request){

        $validator = validator($request->all(), [
            'admin_notification_id' => 'required|numeric|exists:pm_notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $lang = app()->getLocale();

        $detail = \DB::table('pm_notifications')
            ->where('id', $request->admin_notification_id)
            ->select('title','message','message_language','created_at','seen_by','property_manager_id')
            ->first();

        $detail->created_at = date('d-M-Y h:i A', strtotime($detail->created_at));

        //check if seen or not
        $pm_seen = \DB::table('pm_notifications')
            ->where('id', $request->admin_notification_id)
            ->whereRaw('FIND_IN_SET(?,seen_by)', [$request->user()->id])
            ->select('id')
            ->first();

        $has_seen = blank($pm_seen) ? 0 : 1;

        if($has_seen == 0){
            //update in db

            //if send to - all PM
            if($detail->property_manager_id == 0){
                if(!blank($detail->seen_by)){
                    //a if other seen, then merge and update

                    try{
                        \DB::table('pm_notifications')->where('id', $request->admin_notification_id)
                        ->update([
                            'seen_by' => $detail->seen_by.$request->user()->id.',',
                        ]);
                    }catch (\Exception $e){
                        Log::error('----view_admin_notification_for_pm----- '.json_encode($e) );
                    }

                }elseif(blank($detail->seen_by)){
                    //b if seen_by empty, then update

                    \DB::table('pm_notifications')->where('id', $request->admin_notification_id)
                        ->update([
                            'seen_by' => $request->user()->id.',',
                        ]);
                }

            }elseif($detail->property_manager_id == $request->user()->id){
                //if logged in PM only then update

                \DB::table('pm_notifications')->where('id', $request->admin_notification_id)
                    ->update([
                        'seen_by' => $request->user()->id.',',
                    ]);
            }

        }

        unset($detail->seen_by);
        unset($detail->property_manager_id);

        $response = [
            'success' => true,
            'data' =>   $detail,
            'count'    => app('App\Http\Controllers\NotificationController')->common_count_of_admin_notification_for_pm($request,$lang),
            'message' => 'view_admin_notification_for_pm',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //POST
    public function pm_delete_tenant_noti(Request $request){
        $validator = validator($request->all(), [
            'tenant_notification_id' => 'required|numeric|exists:tenant_notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        \DB::table('tenant_notifications')->where('id', $request->tenant_notification_id)->delete();

        return $this->sendResponse([],__(app()->getLocale().'.noti_deleted'),200,200);
    }


     //POST
     public function search_tenant_noti_for_pm(Request $request)
     {
        $lang = app()->getLocale();

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'search_key' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        $tenant_notification_list_query = \App\Models\TenantNotificationModel::leftJoin('tenants', 'tenant_notifications.tenant_id', '=', 'tenants.id')
            ->where('tenant_notifications.pm_company_id', $request->user()->pm_company_id)
            // ->where('tenant_notifications.message_language', $lang)

            ->where(function ($query) use( $search_key ) {
                $query->where('tenant_notifications.title', 'LIKE' , "%$search_key%" )
                    ->orWhere('tenant_notifications.message', 'LIKE', "%$search_key%" )

                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%" )
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%" );
            });

            $tenant_notification_list = $tenant_notification_list_query->select(
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


        $tenant_notification_list_count_query = \App\Models\TenantNotificationModel::leftJoin('tenants', 'tenant_notifications.tenant_id', '=', 'tenants.id')
            ->where('tenant_notifications.pm_company_id', $request->user()->pm_company_id)
            ->where('tenant_notifications.message_language', $lang)
            ->where(function ($query) use( $search_key ) {
                $query->where('tenant_notifications.title', 'LIKE' , "%$search_key%" )
                    ->orWhere('tenant_notifications.message', 'LIKE', "%$search_key%" )

                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%" )
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%" );
            });

        $tenant_notification_list_count = $tenant_notification_list_count_query->count();

        $response = [
            'success' => true,
            'data'    => $tenant_notification_list,
            'message' => 'search_tenant_noti_for_pm',
            'pagecount'  => (int)ceil($tenant_notification_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
     }



     //POST
     // PM noti from admin
     public function search_admin_noti_for_pm(Request $request)
     {
         $lang = app()->getLocale();

         $validator = validator($request->all(), [
             'page' => 'required|numeric',
             'search_key' => 'required',

         ]);

         if ($validator->fails()) {
             return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
         }

         $page = $request->page;
         $skip = $page ? 10 * ($page - 1) : 0;
         $search_key = $request->search_key;

         $admin_notification_list = \App\Models\PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
            // ->where('message_language', $lang)
            ->where(function ($query) use( $search_key ) {
            $query->where('title', 'LIKE' , "%$search_key%" )
                ->orWhere('message', 'LIKE', "%$search_key%" );
            })
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


         $admin_notification_list_count = \App\Models\PmNotificationModel::whereIn('property_manager_id', [0, $request->user()->id])
             ->where('message_language', $lang)
             ->where(function ($query) use( $search_key ) {
                $query->where('title', 'LIKE' , "%$search_key%" )
                    ->orWhere('message', 'LIKE', "%$search_key%" );
                })
             ->count();

         $response = [
             'success' => true,
             'data'    => $admin_notification_list,
             'message' => 'search_admin_noti_for_pm',
             'pagecount'  => (int)ceil($admin_notification_list_count / 10),
             'status'  => 200
         ];
         return response()->json($response, 200);
     }

     public function send_push_noti_for_frontend(Request $request){
        \App\Services\SendTopicPushNotifica::send_topic_push_noti_android($request->topic.'_en',$request->title,$request->message,'frontend_noti','chat',$request->unit_id, $request->request_for_id);
        // \Log::alert('unit id '.$request->unit_id);
        // \Log::alert('request_for_id ' .$request->request_for_id);

<<<<<<< HEAD
        $type = 'chat';
        \App\Services\SendTopicPushNotifica::send_topic_push_noti_android($request->topic,$request->title,$request->message,'frontend_notofication',$type);

     }
=======
        return $this->sendResponse( [], '' ,200,200);
    }
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316

     public function subscribe_topic(Request $request){

        // $appInstance = $messaging->getAppInstance($request->topicsub);

        // /** @var \Kreait\Firebase\Messaging\TopicSubscriptions $subscriptions */

        // $subscriptions = $appInstance->topicSubscriptions();

        // $response = [
        //     'success' => true,
        //     'data'    => "{$subscription->registrationToken()} is subscribed to {$subscription->topic()}\n",
        //     'message' => 'subscribe_topic',
        //     'pagecount'  => 0,
        //     'status'  => 200
        // ];
        // return response()->json($response, 200);

     }

}
