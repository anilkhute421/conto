<?php

namespace App\Services;

use Storage;
use Log;
use DB;
class PmLogService
{

    //\Storage::disk('azure')->put($imageName, \File::get($file));

    //function for delete file
    //module,action,affected_record_id,pm_id,pm_company_id,record_name
    public static function pm_log_entry($module,$action,$affected_record_id,$pm_id,$pm_company_id,$record_name,$log_message){

        try{

            \DB::table('property_manager_logs')->insert([
                'module' => $module,
                'action' => $action,
                'affected_record_id' => $affected_record_id, //all
                'record_name' => mb_strimwidth( $record_name, 0, 10, '.'),
                'pm_company_id' => $pm_company_id,
                'property_manager_id' => $pm_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }catch (\Exception $e){
            Log::error('---------pm_log_entry------ '.$log_message.' ------------- '.$e);
        }
    }

    
    //module,action,affected_record_id,pm_id,pm_company_id,record_name
    public static function pm_log_delete_entry($module,$action,$affected_record_id,$pm_id,$pm_company_id,$record_name,$log_message){

        try{

            \DB::table('property_manager_logs')->insert([
                'module' => $module,
                'action' => $action,
                'affected_record_id' => $affected_record_id, //all
                'record_name' => mb_strimwidth( $record_name, 0, 10, '.'),
                'pm_company_id' => $pm_company_id,
                'property_manager_id' => $pm_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }catch (\Exception $e){
            Log::error('---------pm_log_entry------ '.$log_message.' ------------- '.$e);
        }
    }

    // starting logs.
    // ->buildings - done.(remaining delete)
    // ->available unit - done.(remaining delete)
    // ->unit - done(remaining delete)
    // ->Owners - done(remaining delete,status(c))
    // ->tenant - done(remaining delete,status(c))
    // ->contract - done(remaining delete,status(c))
    // ->payment - done(remaining delete,status(c))
    // ->Maintenance - done(remaining delete,status(c))
    // ->Expenses - done(remaining delete,status(c))
    // ->Expert - done(remaining delete,status(c))
}
