<?php

namespace App\Services;

use Storage;
use Log;
use DB;
class FileUploadService
{

    //\Storage::disk('azure')->put($imageName, \File::get($file));

    //function for delete file
    public static function delete_from_azure($file_name,$log_message){

        //Log::info($file_name);
        try{
            if(Storage::disk('azure')->exists($file_name)) {
                Storage::disk('azure')->delete($file_name);
               // Log::info('deleted');
            }
            //Log::info(json_encode(Storage::disk('azure')->exists($file_name)));
        }catch (\Exception $e){
            Log::error('---------delete_from_azure------ '.$log_message.' ------------- '.$e);
        }
    }

    public static function upload_to_azure($imageName,$file,$log_message){
        try{

            \Storage::disk('azure')->put($imageName, file_get_contents($file));

        }catch (\Exception $e){
            Log::error('---------upload_to_azure--------- '.$log_message.' ------------- '.$e);
        }
    }

    public static function delete_contract_document_from_azure($file_name,$log_message){
        //Log::info($file_name);
        try{
            if(Storage::disk('azure_documents')->exists($file_name)) {
                Storage::disk('azure_documents')->delete($file_name);
               // Log::info('deleted');
            }
            //Log::info(json_encode(Storage::disk('azure')->exists($file_name)));
        }catch (\Exception $e){
            Log::error('---------delete_contract_document_from_azure------ '.$log_message.' ------------- '.$e);
        }
    }



    public static function upload_contract_to_azure_doc($imageName,$file,$log_message){
        try{

            \Storage::disk('azure_documents')->put($imageName, file_get_contents($file));

        }catch (\Exception $e){
            Log::error('---------upload_contract_to_azure_doc--------- '.$log_message.' ------------- '.$e);
        }
    }


    public static function delete_attachment_by_id_from_azure($file_name,$log_message){
        //Log::info($file_name);
        try{
            if(Storage::disk('azure')->exists($file_name)) {
                Storage::disk('azure')->delete($file_name);
               // Log::info('deleted');
            }
            //Log::info(json_encode(Storage::disk('azure')->exists($file_name)));
        }catch (\Exception $e){
            Log::error('---------delete_attachment_by_id_from_azure------ '.$log_message.' ------------- '.$e);
        }
    }

    public static function upload_expenses_files($imageName,$file,$log_message){
        try{

            \Storage::disk('azure_documents')->put($imageName, file_get_contents($file));

        }catch (\Exception $e){
            Log::error('---------upload_contract_to_azure_doc--------- '.$log_message.' ------------- '.$e);
        }
    }


    // upload_video_to_azure_videos


//     public static function upload_video_to_azure_videos($folder_name_in_bucket,$file_name,$file,$log_message){
//         $filePath = $folder_name_in_bucket.'/'.$file_name;
//         try{
//             Storage::disk('s3')->put( $filePath, file_get_contents($file),'public' );
//         }catch (\Exception $e){
//             Log::error('------------------ '.$log_message.' ------------------ '.$e);
//         }
//     }


//     #this code is for best for uploading large files on  s3.
//     public static function upload_large_to_s3($folder_name_in_bucket,$file_name,$sourceFile,$log_message){
//         ini_set('max_execution_time' , 0);
//         $filePath = $folder_name_in_bucket.'/'.$file_name;
//         try{
//             $resource = fopen($sourceFile->getRealPath(), 'r+');
//             \Storage::disk('s3')->put( $filePath, $resource);
//            // Log::info('..file uploaded..'. Storage::disk('s3')->url($filePath));
//         }catch (\Exception $e){
//             Log::error('------------------ '.$log_message.' ------------------ '.$e);
//         }
//     }
// //function for update file name entry in associated table
//     public static function update_file_name($table_name, $where_field_name, $where_field_value, $update_field_name, $update_field_value){
//         DB::table($table_name)
//             ->where($where_field_name, $where_field_value)
//             ->update([
//                 $update_field_name => $update_field_value
//             ]);
//     }



//     //function for making s3 url for project documents
//     public static function get_project_documents_s3_url($file_name){
//         if(Storage::disk('s3')->exists('project_images/'.$file_name)) {
//             return Storage::disk('s3')->url('project_images/'.$file_name);
//         }
//     }

}
