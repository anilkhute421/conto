<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiBaseController;
use Illuminate\Foundation\Auth\AuthenticatePropertyManager;
use App\Http\Requests\Api\UserRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\PropertyManager;
use App\Models\CountryCurrencyModel;
use App\Models\RolesModel;
use App\Models\PropertyManagerCompany;
use Hash;
use Mail;
use Image;
use App\Helpers\Helper;

use Illuminate\Http\Request;

class PropertyManagerTwoController extends ApiBaseController
{
    public function login (Request $request){
        $validator = validator($request->all(), UserRequests::login_rules());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(), 201 ,200);
        }
        $user = PropertyManager::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // return $this->sendSingleFieldError('Invalid credentials entered',201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.invalid_credentials'),201,200);

        }

        if ( $user->status == 0) {
            // return $this->sendSingleFieldError('account deactivated, contact admin at contolio@contolio.com', 201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.account_deactivated'), 201,200);

        }
        // $user->tokens()->delete();
        // $result = $user;
        $otp = mt_rand(1000,9999);
        PropertyManager::where('email', $request->email )->update(['email_otp' => $otp ]);
        // Mail::to($request->email)->send(new \App\Mail\SendOtp($otp));
        // $result['country_name'] = CountryCurrencyModel::where('id' , $user->country_id )->first()->value('country');
        // $result['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
        // RolesModel::where('id' , $user->role_id )->first()->value('role_title'):'';
        // $result['token'] = $user->createToken('pm' , ['property_manager'])->plainTextToken;
        // $result['company_name'] = PropertyManagerCompany::where('id' , $user->pm_company_id )->first()->value('name');
        return $this->sendResponse([],__(app()->getLocale().'.otp_sent_to_email'),200,200);

    }


    public function upload_image(Request $request){
        if ($request->hasFile('photo')) {
            $image      = $request->file('photo');
            $fileName   = uniqid() . '.' . $image->getClientOriginalExtension();
            \Storage::disk('azure')->put($fileName, \File::get($request->photo));
        }
    }

    public function verify_otp(Request $request){
        $validator = validator($request->all(), ['email' => 'required|email|exists:property_managers,email' , 'otp' => 'required']);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $exists = PropertyManager::where(['email' => $request->email , 'email_otp' => $request->otp ])->exists();
        if($exists){
            $user = PropertyManager::where('email', $request->email)->first();
            $user->tokens()->delete();
            $result = $user;
            $result['country_name'] = \App\Models\Country::where('id' , $user->country_id )->value('country');

            $result['role_name'] = (RolesModel::where('id' , $user->role_id )->exists())?
            RolesModel::where('id' , $user->role_id )->first()->value('role_title'):'';

            $result['role_details'] = \DB::table('roles')->where('id',$user->role_id)->first();

            $result['token'] = $user->createToken('pm' , ['property_manager'])->plainTextToken;

            $result['company_name'] = PropertyManagerCompany::where('id' , $user->pm_company_id )->value('name');

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('profile','login',$user->id,$user->id,$user->pm_company_id,'login', 'pm_login');

            return $this->sendResponse($result,__(app()->getLocale().'.login_success'),200,200);
        }else{
            // return $this->sendSingleFieldError('Invalid otp entered',201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.invalid_otp'),201,200);

        }

    }


    public function forgot_password_email(Request $request)
    {
        $validator = validator($request->all(), ['email' => 'required|email|exists:property_managers,email']);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $user = PropertyManager::where('email', $request->email )->first();
        $password = Helper::randomPassword();
        $user->update(['password' => Hash::make($password)]);
        $emailData =  array(
            'password'   => $password
        );
        Mail::to($request->email)->send(new \App\Mail\SendPassword($emailData));
        // return $this->sendResponse( [] , 'Generated password has been sent to your email.' ,200,200);
        return $this->sendResponse( [] , __(app()->getLocale().'.password_sent_to_your_email') ,200,200);
     }

     
}
