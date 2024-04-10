<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiBaseController;


class MobileApiController extends ApiBaseController
{
    public function mobile_app_versions_check(){

        $result = \DB::table('mobile_app_versions')->select('android','ios')->first();

        return $this->sendResponse($result,'mobile_app_versions_check',200,200);
    }


    public function admin_update(Request $request){

        $admin_id = \DB::table('mobile_app_versions')->value('id');

        \DB::table('mobile_app_versions')->where('id', $admin_id)->update([
            'android' => $request->android , 'ios' => $request->ios
        ]);

        return $this->sendResponse([],'admin_update',200,200);
    }

}
