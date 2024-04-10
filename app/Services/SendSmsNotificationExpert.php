<?php

namespace App\Services;

use Storage;
use Log;
use DB;
class SendSmsNotificationExpert
{

    public static function smsNotification($phone, $maintanence_request_details, string $expert_name, string $unique_code,string $email, $visit_datetime)
    {
        $twilio = new \Twilio\Rest\Client(\Config::get('constants.TWILIO_AUTH_SID'), \Config::get('constants.TWILIO_AUTH_TOKEN'));

        $body = "Dear " .ucfirst($expert_name)  ."\n". 'Maintenance request details: '.
         'Tenant name'.': '.$maintanence_request_details->first_name ." ". $maintanence_request_details->last_name."\n".
         'Tenant phone'.': '.'+'.$maintanence_request_details->country_code.' '.$maintanence_request_details->phone ."\n".
         'Building name'.': '.$maintanence_request_details->building_name."\n".
         'Unit no'.': ' .$maintanence_request_details->unit_no."\n".
         'Request ID'.': '.$maintanence_request_details->request_code."\n".
         'Status'.': '.$maintanence_request_details->status."\n".
         'Request for'.': '.$maintanence_request_details->request_for."\n".
         'Address'.': '.$maintanence_request_details->address."\n".
         \Config::get('constants.WEBSITE_LINK').$unique_code;
        try {

            $twilio->messages->create("+$phone", ["from" => \Config::get('constants.TWILIO_SMS_FROM_NO'), "body" => $body ]);

            $inputs = [];
            $inputs['expert_name'] = $expert_name;
            $inputs['tenant_name'] = $maintanence_request_details->first_name ." ". $maintanence_request_details->last_name;
            $inputs['tenant_phone'] = '+'.$maintanence_request_details->country_code.' '.$maintanence_request_details->phone;
            $inputs['building_name'] = $maintanence_request_details->building_name;
            $inputs['unit_no'] =  $maintanence_request_details->unit_no;
            $inputs['request_id'] =  $maintanence_request_details->request_code;
            $inputs['status'] =  $maintanence_request_details->status;
            $inputs['request_for'] =  $maintanence_request_details->request_for;
            $inputs['description'] =  $maintanence_request_details->description;
            $inputs['visit_datetime'] = $visit_datetime;

            $inputs['address'] =  $maintanence_request_details->address;
            $inputs['file_details'] =  \Config::get('constants.WEBSITE_LINK').$unique_code;

            \Mail::to($email)->send(new \App\Mail\SendMainRequestDetailsToExpert($inputs));
            // \Mail::to('sfs.naveen18@gmail.com')->send(new \App\Mail\SendMainRequestDetailsToExpert($inputs));

        } catch (\Exception $e) {
            \Log::error('---------smsNotification----------- ' . json_encode($e) );
        }

    }
}
