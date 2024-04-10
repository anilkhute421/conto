<?php

namespace App\Http\Controllers;


use App\Models\TenantModel;

class HomeController extends Controller
{
    public function change_password ($unique_email_key , $user_id){
        $tenets = TenantModel::where(['id' => $user_id , 'unique_email_key' => $unique_email_key ])
        ->select('email_key_expire')->first();
        if (!blank($tenets)){
            dd($tenets);
        }else{
        }
    }

    public function index(){
         return view('welcome');
    }

    public function getDownload(){
        $file= public_path(). "/download/file.xlsx";
        $headers = array(
                'Content-Type: application/xlsx',
                );
        return response()->download($file, 'file.xlsx', $headers);
    }

    public function excel_export($pm_id , $export_type){
        \Artisan::call('excel:export', [
            'pm_id' => $pm_id ,
             'export_type' => $export_type
        ]);
    }


}
