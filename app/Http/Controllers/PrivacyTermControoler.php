<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TenantModel;
use Illuminate\Http\Request;
use App\Http\Requests\MobileRequest;

class PrivacyTermControoler extends Controller
{
//     public function tenant_privacy_policy(Request  $request){
//       return view('privacy_term.privacy_policy');
//    }

   public function forgot_password($key){
      $userid = TenantModel::where('email_url_key', $key)->value('id');
       return view('privacy_term.tenant_forgot_password')->with('userid',$userid);
   }

//    public function admin_forgot_password($key){
//         $userid = \DB::table('admins')->where('email_verify_code', $key)->value('id');
//         return view('privacy_term.admin_forgot_password')->with('userid',$userid);
//    }

   public function post_change_password(Request  $request){
      $validator = validator($request->all(), MobileRequest::forget_password_rules());
      if($validator->fails()){
      return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
        }
      $request['password'] = \Hash::make($request['password']);
      $passwordsucsess = TenantModel::where('id', $request->user_id)
      ->update(['password' => $request->password]);

       if(!blank($passwordsucsess)) {
         return view('privacy_term.success_message')->with('success', 'Your password has been changed successfully');
      }
   }


//    public function tenant_term_condition(Request  $request){
//       return view('privacy_term.terms_condition');
//    }


}
