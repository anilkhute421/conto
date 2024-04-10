<?php
return [
    'lang_key' =>   env('LANG_KEY', 'Lang'),
    'image_base_url' => env('AZURE_STORAGE_URL', 'https://contoliostoragev.blob.core.windows.net/contolioimages'),
    'file_base_url' => env('AZURE_DOCUMENTS_URL','https://contoliostoragev.blob.core.windows.net/contoliodocument'),
    'video_base_url' => env('AZURE_VIDEOS_URL','https://contoliostoragev.blob.core.windows.net/contoliovideos'),
    'TWILIO_AUTH_SID'   => env('TWILIO_AUTH_SID'),
    'TWILIO_AUTH_TOKEN'  =>  env('TWILIO_AUTH_TOKEN'),
    'TWILIO_WHATSAPP_FROM' => env("TWILIO_WHATSAPP_FROM"),
    'video_thumb_url' => 'https://contoliopmweb.azurewebsites.net/static/media/VideoThumb.5918155a.png',//not in use

    'PRIVATE_KEY_ID' => env('PRIVATE_KEY_ID'),
    'PRIVATE_KEY' => env('PRIVATE_KEY'),
    'CLIENT_EMAIL'  => env('CLIENT_EMAIL'),
    'CLIENT_ID' => env('CLIENT_ID'),
    'CLIENT_X509_CERT_URL' => env('CLIENT_X509_CERT_URL'),
    'WEBSITE_LINK' => env('WEBSITE_LINK'),
    'TWILIO_SMS_FROM_NO' => "+19124933801"


];
