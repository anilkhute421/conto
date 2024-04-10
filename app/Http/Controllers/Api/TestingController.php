<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Http\Request;
use DB;
use Validator;
use Input;
use Carbon\Carbon;
use App\Http\Controllers\Api\ApiBaseController;


class TestingController extends ApiBaseController
{


    public function test_final(Request $request){

        $date = Carbon::now()->toDateTimeString();

        dd($date);

        \App::setLocale('en');

        // $lang = app()->getLocale();

        // dd($lang);

        $attributeNames = array(
            'monthly_rent' => __(app()->getLocale().'.monthly_rent'),
         );

         $rules = ['monthly_rent' => 'required|numeric'];
        //  $validator = Validator::make ( $request->all(), $rules );
         $validator = validator($request->all(), $rules);

        //  if(app()->getLocale() == 'ar'){
            $validator->setAttributeNames($attributeNames);
        //  }

         if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

    }

    

    
}
