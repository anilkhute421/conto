<?php

namespace App\Services;

use Storage;
use Log;
use DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class SendTopicPushNotifica
{

    public static function send_topic_push_noti_android($topic_name,$title,$messa,$log_message,$msg_type,$unit_id,$request_for_id=0,$message_language='en'){
        try{
            $credentilas = [
                "type" => "service_account",
                "project_id" => "contolio",
                "private_key_id" => \Config::get('constants.PRIVATE_KEY_ID'),
                "private_key" => \Config::get('constants.PRIVATE_KEY'),
                "client_email" => \Config::get('constants.CLIENT_EMAIL'),
                "client_id" => \Config::get('constants.CLIENT_ID'),
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => \Config::get('constants.CLIENT_X509_CERT_URL')
              ];

              $credentilas =  json_encode($credentilas);
              $factory = (new Factory)->withServiceAccount($credentilas);
              $messaging = $factory->createMessaging();
              $topic  = strtolower(str_replace(" ","", $topic_name));
              $message = CloudMessage::withTarget('topic', $topic)
              ->withNotification(Notification::create((string)$title, (string)$messa))
              ->withData(['msg_type' => $msg_type, 'unit_id' => (int)$unit_id, 'request_for_id' => (int)$request_for_id , 'message_language' => (string)$message_language]);

              $res = $messaging->send($message);
            //   \Log::warning(json_encode($res));

        }catch (\Exception $e){
            Log::error('---------send_topic_push_noti_android------ '.$log_message.' ------------- '.$e);
        }

    }

}
