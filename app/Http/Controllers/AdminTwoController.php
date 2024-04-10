<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\AdminModel;
use App\Models\PropertyManager;
use App\Models\PropertyManagerCompany;
use App\Models\TenantModel;
use App\Models\RolesModel;
use App\Models\BuildingModel;
use App\Models\AvailableUnitModel;
use App\Models\ContactRequestModel;
use App\Models\CmsModel;
use App\Models\CountryCurrencyModel;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AdminRequests;
use DB;

class AdminTwoController extends ApiBaseController
{

 //get
 public function cms_page_for_dropdown(){
    \DB::statement("SET SQL_MODE=''");
    $user = CmsModel::select( 'page_for', 'id')
    ->groupBy('page_for')->get()
    ->transform(function($item) {
        $item->page_for = $item->page_for == 1 ? 'Tenant' : 'Property Manager';
        return $item;
    });
    return $this->sendResponse($user,'user_dropdown',200,200);
}


  //get
  public function host_units(){
    $companies = PropertyManagerCompany::pluck( 'name', 'id');
    $host_units = BuildingModel::select(
        'building_name',
        'address',
        'pm_company_id as company_id',
        'status',
        'id',
    )
    ->get()
    ->transform(function($item) use($companies) {
        $item->company_name = $companies[$item->company_id];
        $item->unit_no = \DB::table('tenants_units')->where('building_id', $item->id)->count();

        $item->status = $item->status == 1 ? 'Active' : 'Deactive';
        $item->address =  mb_strimwidth( $item->address, 0, 15, '...');
        return $item;
    });
   return $this->sendResponse($host_units,'host_units',200,200);
  }

  //post
  public function view_host_units(Request $request){
    $AvailableUnit = BuildingModel::select(
        'building_name',
        'address',
        'location_link',
        'status',
        'pm_company_id',
        'property_manager_id',
        'status',
        'id',
    )
    ->where('id',$request->view_host_units)
    ->first();
    $AvailableUnit->status = $AvailableUnit->status == 1 ? 'Active' : 'Deactive';
    $AvailableUnit->unit_no = \DB::table('tenants_units')->where('building_id', $AvailableUnit->id)->count();

    $AvailableUnit->company_name = \DB::table('property_manager_companies')->where('id', $AvailableUnit->pm_company_id)->value('name');
    $AvailableUnit->pm_name  = \DB::table('property_managers')->where('id', $AvailableUnit->property_manager_id)->value('name');
     return $this->sendResponse($AvailableUnit, 'Available Unit' ,200,200);
  }


  //get
  public function contact_request(){
    $contact_request = ContactRequestModel::select(
        'contact_requests.id',
        'contact_requests.description',
        'contact_requests.created_at',
        'property_managers.name',
       )
    ->join('property_managers' , 'property_managers.id', '=' , 'contact_requests.property_manager_id')
    ->get()
    ->transform(function($item)  {
        $item->date = date('d M Y', strtotime($item->created_at));
        $item->description =  mb_strimwidth( $item->description, 0, 25, '...');
        return $item;
    });
    return $this->sendResponse($contact_request,'contact_request',200,200);
  }


  //post
  public function view_contact_request(Request $request){
    $companies = PropertyManagerCompany::pluck( 'name', 'id');
    $contact_request_details = ContactRequestModel::where('contact_requests.id',$request->view_contact_request)->select(
        'contact_requests.id',
        'contact_requests.description',
        'property_managers.name',
        'property_managers.pm_company_id  as company_id'
       )
    ->join('property_managers' , 'property_managers.id', '=' , 'contact_requests.property_manager_id')
    ->get()
    ->transform(function($item) use($companies) {
        $item->date = date('d M Y', strtotime($item->created_at));
        $item->company_name = $companies[$item->company_id];
      return $item;
    })->first();
    return $this->sendResponse($contact_request_details,'contact_request_details',200,200);
  }

  //get
   public function manage_cms(){
      $contact_request = CmsModel::select(
          'cms_pages.id',
          'cms_pages.title',
          'cms_pages.page_for',
          'cms_pages.page_language',
          'cms_pages.updated_at',
         )
      ->get()
      ->transform(function($item)  {
          $item->date = date('d M Y', strtotime($item->updated_at));
          $item->page_language = $item->page_language == 'en' ? 'English' : 'Arabic';


          $item->page_for = $item->page_for == 1 ? 'Tenant' : 'Property manager';
          return $item;
      });
      return $this->sendResponse($contact_request,'cms_details',200,200);
    }

    //post
    public function edit_manage_cms(Request $request){
        $userId = $request->cms_id;

        $validator = validator($request->all(), AdminRequests::edit_manage_cms($userId));
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $final = [];
        $final['description'] = $request->description;
        CmsModel::where('id', $userId)->update($final);
        return $this->sendResponse( [] , 'Details updated successfully' ,200,200);
    }

    //post
    //view_cms
    public function view_cms(Request $request){
        $cms= CmsModel::where('id',$request->cms_id)
            ->select('cms_pages.id',
            'cms_pages.title',
            'cms_pages.page_for',
            'cms_pages.page_language',
            'cms_pages.description')
            ->first();
        $cms->page_language = $cms->page_language == 'en' ? 'English' : 'Arabic';
        $cms->page_for = $cms->page_for == 1 ? 'Tenant' : 'Property manager';

        return $this->sendResponse($cms, 'cms_data' ,200,200);
    }


    public function pm_en_term_condition(){
        $pm_en_term_condition =  CmsModel::where('id','7')->value('description');
        return view('admin/pm_en_term_condition')->with('pm_en_term_condition',$pm_en_term_condition);

    }

    public function pm_ar_term_condition(){
        $pm_ar_term_condition =  CmsModel::where('id','8')->value('description');
        return view('admin/pm_ar_term_condition')->with('pm_ar_term_condition',$pm_ar_term_condition);
    }

    public function tenant_en_term_condition(){
        $tenant_en_term_condition =  CmsModel::where('id','5')->value('description');
        return view('admin/tenant_en_term_condition')->with('tenant_en_term_condition',$tenant_en_term_condition);
    }

    public function tenant_ar_term_condition(){
        $tenant_ar_term_condition =  CmsModel::where('id','6')->value('description');
        return view('admin/tenant_ar_term_condition')->with('tenant_ar_term_condition',$tenant_ar_term_condition);
    }

    public function pm_en_privacy_policy(){
        $pm_en_privacy_policy =  CmsModel::where('id','3')->value('description');
        return view('admin/pm_en_privacy_policy')->with('pm_en_privacy_policy',$pm_en_privacy_policy);
    }

    public function pm_ar_privacy_policy(){
        $pm_ar_privacy_policy =  CmsModel::where('id','4')->value('description');
        return view('admin/pm_ar_privacy_policy')->with('pm_ar_privacy_policy',$pm_ar_privacy_policy);
    }

    public function tenant_en_privacy_policy(){
        $tenant_en_privacy_policy =  CmsModel::where('id','1')->value('description');
        return view('admin/tenant_en_privacy_policy')->with('tenant_en_privacy_policy',$tenant_en_privacy_policy);
    }

    public function tenant_ar_privacy_policy(){
        $tenant_ar_privacy_policy =  CmsModel::where('id','2')->value('description');
        return view('admin/tenant_ar_privacy_policy')->with('tenant_ar_privacy_policy',$tenant_ar_privacy_policy);
    }

    //get
    public function role_list(){
        $role_list = RolesModel::select(
            'id',
            'role_title',
            'created_at',
            )
        ->get()
        ->transform(function($item)  {
            $item->date = date('d M Y', strtotime($item->created_at));

            return $item;
        });

        return $this->sendResponse($role_list,'role_list',200,200);
    }


    public function create_role(Request $request){

        $validator = validator($request->all(), ['role_title'  =>  'max:20|unique:roles,role_title']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        \DB::table('roles')->insert([

            'role_title' => $request->role_title,

            'buildings_management_create' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_create,
            'buildings_management_view' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_view,
            'buildings_management_edit' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_edit,
            'buildings_management_delete' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_delete,
            'buildings_management_none' =>  $request->buildings_management_none,

            'contracts_management_create' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_create,
            'contracts_management_view' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_view,
            'contracts_management_edit' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_edit,
            'contracts_management_delete' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_delete,
            'contracts_management_none' =>  $request->contracts_management_none,

            'payment_management_create' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_create,
            'payment_management_view' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_view,
            'payment_management_edit' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_edit,
            'payment_management_delete' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_delete,
            'payment_management_none' =>  $request->payment_management_none,

            'tenant_management_create' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_create,
            'tenant_management_view' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_view,
            'tenant_management_edit' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_edit,
            'tenant_management_delete' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_delete,
            'tenant_management_none' =>  $request->tenant_management_none,

            'units_management_create' => ($request->units_management_none == 1) ? 0 : $request->units_management_create,
            'units_management_view' => ($request->units_management_none == 1) ? 0 : $request->units_management_view,
            'units_management_edit' => ($request->units_management_none == 1) ? 0 : $request->units_management_edit,
            'units_management_delete' => ($request->units_management_none == 1) ? 0 : $request->units_management_delete,
            'units_management_none' =>  $request->units_management_none,

            'avail_unit_create' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_create,
            'avail_unit_view' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_view,
            'avail_unit_edit' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_edit,
            'avail_unit_delete' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_delete,
            'avail_unit_none' =>  $request->avail_unit_none,

            'maintenance_req_create' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_create,
            'maintenance_req_view' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_view,
            'maintenance_req_edit' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_edit,
            'maintenance_req_delete' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_delete,
            'maintenance_req_none' =>  $request->maintenance_req_none,

            'expert_create' => ($request->expert_none == 1) ? 0 : $request->expert_create,
            'expert_view' => ($request->expert_none == 1) ? 0 : $request->expert_view,
            'expert_edit' => ($request->expert_none == 1) ? 0 : $request->expert_edit,
            'expert_delete' => ($request->expert_none == 1) ? 0 : $request->expert_delete,
            'expert_none' =>  $request->expert_none,

            'expense_create' => ($request->expense_none == 1) ? 0 : $request->expense_create,
            'expense_view' => ($request->expense_none == 1) ? 0 : $request->expense_view,
            'expense_edit' => ($request->expense_none == 1) ? 0 : $request->expense_edit,
            'expense_delete' => ($request->expense_none == 1) ? 0 : $request->expense_delete,
            'expense_none' =>  $request->expense_none,

            'owner_create' => ($request->owner_none == 1) ? 0 : $request->owner_create,
            'owner_view' => ($request->owner_none == 1) ? 0 : $request->owner_view,
            'owner_edit' => ($request->owner_none == 1) ? 0 : $request->owner_edit,
            'owner_delete' => ($request->owner_none == 1) ? 0 : $request->owner_delete,
            'owner_none' =>  $request->owner_none,

            'amount_view' => ($request->amount_none == 1) ? 0 : $request->amount_view,
            'amount_none' => $request->amount_none,

            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->sendResponse([],'create_role',200,200);
    }


    //post
    public function view_role(Request $request){
        $role = \DB::table('roles')->where('id', $request->role_id)->first();

        return $this->sendResponse($role, 'view_role' ,200,200);
    }


    public function update_role(Request $request){

        $validator = validator($request->all(), [
            'role_id'=> 'required|numeric|exists:roles,id',
            'role_title'  =>  'max:20|unique:roles,role_title,'.$request->role_id,
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        \DB::table('roles')
        ->where('id',$request->role_id)
        ->update([

            'role_title' => $request->role_title,

            'buildings_management_create' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_create,
            'buildings_management_view' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_view,
            'buildings_management_edit' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_edit,
            'buildings_management_delete' => ($request->buildings_management_none == 1) ? 0 : $request->buildings_management_delete,
            'buildings_management_none' =>  $request->buildings_management_none,

            'contracts_management_create' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_create,
            'contracts_management_view' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_view,
            'contracts_management_edit' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_edit,
            'contracts_management_delete' => ($request->contracts_management_none == 1) ? 0 : $request->contracts_management_delete,
            'contracts_management_none' =>  $request->contracts_management_none,

            'payment_management_create' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_create,
            'payment_management_view' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_view,
            'payment_management_edit' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_edit,
            'payment_management_delete' => ($request->payment_management_none == 1) ? 0 : $request->payment_management_delete,
            'payment_management_none' =>  $request->payment_management_none,

            'tenant_management_create' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_create,
            'tenant_management_view' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_view,
            'tenant_management_edit' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_edit,
            'tenant_management_delete' => ($request->tenant_management_none == 1) ? 0 : $request->tenant_management_delete,
            'tenant_management_none' =>  $request->tenant_management_none,

            'units_management_create' => ($request->units_management_none == 1) ? 0 : $request->units_management_create,
            'units_management_view' => ($request->units_management_none == 1) ? 0 : $request->units_management_view,
            'units_management_edit' => ($request->units_management_none == 1) ? 0 : $request->units_management_edit,
            'units_management_delete' => ($request->units_management_none == 1) ? 0 : $request->units_management_delete,
            'units_management_none' =>  $request->units_management_none,

            'avail_unit_create' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_create,
            'avail_unit_view' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_view,
            'avail_unit_edit' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_edit,
            'avail_unit_delete' => ($request->avail_unit_none == 1) ? 0 : $request->avail_unit_delete,
            'avail_unit_none' =>  $request->avail_unit_none,

            'maintenance_req_create' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_create,
            'maintenance_req_view' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_view,
            'maintenance_req_edit' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_edit,
            'maintenance_req_delete' => ($request->maintenance_req_none == 1) ? 0 : $request->maintenance_req_delete,
            'maintenance_req_none' =>  $request->maintenance_req_none,

            'expert_create' => ($request->expert_none == 1) ? 0 : $request->expert_create,
            'expert_view' => ($request->expert_none == 1) ? 0 : $request->expert_view,
            'expert_edit' => ($request->expert_none == 1) ? 0 : $request->expert_edit,
            'expert_delete' => ($request->expert_none == 1) ? 0 : $request->expert_delete,
            'expert_none' =>  $request->expert_none,

            'expense_create' => ($request->expense_none == 1) ? 0 : $request->expense_create,
            'expense_view' => ($request->expense_none == 1) ? 0 : $request->expense_view,
            'expense_edit' => ($request->expense_none == 1) ? 0 : $request->expense_edit,
            'expense_delete' => ($request->expense_none == 1) ? 0 : $request->expense_delete,
            'expense_none' =>  $request->expense_none,

            'owner_create' => ($request->owner_none == 1) ? 0 : $request->owner_create,
            'owner_view' => ($request->owner_none == 1) ? 0 : $request->owner_view,
            'owner_edit' => ($request->owner_none == 1) ? 0 : $request->owner_edit,
            'owner_delete' => ($request->owner_none == 1) ? 0 : $request->owner_delete,
            'owner_none' =>  $request->owner_none,

            'amount_view' => ($request->amount_none == 1) ? 0 : $request->amount_view,
            'amount_none' => $request->amount_none,

            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        //if any pm have this role then he will automatically logout
        $_all_pm = PropertyManager::where('role_id', $request->role_id)->get();

            foreach($_all_pm as $pm){

                //log out that pm
                $pm->tokens()->delete();

            }

        return $this->sendResponse([],'update_role',200,200);
    }


    //POST
    public function pm_notification_list(Request $request)
    {

        // $validator = validator($request->all(), [
        //     'page' => 'required|numeric',

        // ]);
        // if ($validator->fails()) {
        //     return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        // }
        // $page = $request->page;
        // $skip = $page ? 10 * ($page - 1) : 0;

        $pm_notification_list = \App\Models\PmNotificationModel::
            leftJoin('property_managers', 'pm_notifications.property_manager_id', '=', 'property_managers.id')
            // where('message_language', $lang)
            ->select(
                'pm_notifications.id',
                'pm_notifications.title',
                'pm_notifications.message',
                'pm_notifications.created_at',
                'pm_notifications.property_manager_id',

                'property_managers.name',
            )
            // ->take(10)
            // ->skip($skip)
            ->orderBy('pm_notifications.id', 'DESC')
            ->get()
            ->transform(function ($row) use($request) {

                $row->message =  mb_strimwidth($row->message, 0, 10, '...');

                $row->title =  mb_strimwidth($row->title, 0, 10, '...');

                $row->date = date('d-M-Y h:i A', strtotime($row->created_at));

                $row->send_to = blank($row->name) ? 'All' : $row->name ;

                unset($row->created_at);
                unset($row->name);

                return $row;
            });

        $response = [
            'success' => true,
            'data'    => $pm_notification_list,
            'message' => 'pm_notification_list',
//         'pagecount'  => (int)ceil($tenant_notification_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    // GET
    // tenant table status
    // 0 - Pending Approval   req
    // 1 - Approved
    // 2- Declined    req
    // 3- Disconnected
    public function tenant_drop_down_at_admin_notification(Request $request){
        $listing = \DB::table('tenants')
            ->whereIn('status',[1,3])
            ->select('first_name', 'last_name', 'id')
            ->get()
            ->transform(function ($row) {
                $row->name = $row->first_name . ' ' . $row->last_name;
                unset($row->first_name);
                unset($row->last_name);

                return $row;
            });
        return $this->sendResponse($listing ,'tenant_drop_down_at_admin_notification',200,200);
    }

    // GET
    public function pm_drop_down_at_admin_notification(Request $request){
        $listing = \DB::table('property_managers')
            ->where('status',1)
            ->select('name', 'id')
            ->get();
        return $this->sendResponse($listing ,'pm_drop_down_at_admin_notification',200,200);
    }


    //post
    public function add_pm_notification(Request $request){

        $validator = validator($request->all(), AdminRequests::add_pm_notification());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        //all pm
        if($request->all_or_select == 0){

            \DB::table('pm_notifications')->insert([
                'title' => $request->title,
                'message' => $request->message,
                'property_manager_id' => 0,
                'message_language' => $request->message_language,
                'seen_by' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        }else{

            //to selected pm
            foreach($request->property_manager_id as $pm_id){

                \DB::table('pm_notifications')->insert([
                    'title' => $request->title,
                    'message' => $request->message,
                    'property_manager_id' => $pm_id,
                    'message_language' => $request->message_language,
                    'seen_by' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }//foreach

        }

        return $this->sendResponse( [] , 'Notification sent successfully' ,200,200);
    }


    //post
    public function tenant_notification_list(Request $request){

        $tenant_notification_list = \App\Models\TenantNotificationModel::
            leftJoin('tenants', 'tenant_notifications.tenant_id', '=', 'tenants.id')
            // where('message_language', $lang)
            ->where('tenant_notifications.pm_company_id', 0)
            ->where('tenant_notifications.property_manager_id', 0)

            ->select(
                'tenant_notifications.id',
                'tenant_notifications.title',
                'tenant_notifications.message',
                'tenant_notifications.created_at',
                'tenant_notifications.tenant_id',

                'tenants.first_name',
                'tenants.last_name',
            )
            // ->take(10)
            // ->skip($skip)
            ->orderBy('tenant_notifications.id', 'DESC')
            ->get()
            ->transform(function ($row) use($request) {

                $row->message =  mb_strimwidth($row->message, 0, 10, '...');

                $row->title =  mb_strimwidth($row->title, 0, 10, '...');

                $row->date = date('d-M-Y h:i A', strtotime($row->created_at));

                $row->send_to = blank($row->first_name) ? 'All' : $row->first_name.' '.$row->last_name ;

                unset($row->created_at);
                unset($row->first_name);
                unset($row->last_name);

                return $row;
            });

        $response = [
            'success' => true,
            'data'    => $tenant_notification_list,
            'message' => 'tenant_notification_list',
//         'pagecount'  => (int)ceil($tenant_notification_list_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }

    //post
    public function view_pm_notification(Request $request){

        $data = \DB::table('pm_notifications')->where('id', $request->id)->first();

        $data->date = date('d-M-Y h:i A', strtotime($data->created_at));

        if($data->property_manager_id == 0){
            $data->send_to = 'All';
        }else{
            $data->send_to = \DB::table('property_managers')->where('id', $data->property_manager_id)->value('name');
        }

        return $this->sendResponse($data, 'view_pm_notification' ,200,200);
    }


    //post
    public function delete_pm_notification(Request $request){
        \DB::table('pm_notifications')->where('id', $request->id)->delete();
        return $this->sendResponse([], 'Deleted successfully' ,200,200);
    }


    //post
    public function delete_tenant_notification(Request $request){
        \DB::table('tenant_notifications')->where('id', $request->id)->delete();
        return $this->sendResponse([], 'Deleted successfully' ,200,200);
    }


    //post
    public function view_tenant_notification(Request $request){

        $data = \DB::table('tenant_notifications')->where('id', $request->id)->first();

        $data->date = date('d-M-Y h:i A', strtotime($data->created_at));

        if($data->tenant_id == 0){
            $data->send_to = 'All';
        }else{
            $send_to = \DB::table('tenants')->where('id', $data->tenant_id)->first();
            $data->send_to = $send_to->first_name.' '.$send_to->last_name;
        }

        return $this->sendResponse($data, 'view_tenant_notification' ,200,200);
    }

    //post
    //send_notification
    public function add_tenant_notification(Request $request){

        ini_set('max_execution_time', 0);

        $validator = validator($request->all(), AdminRequests::add_tenant_notification());
        //\Log::info('running if');

        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        //all tenant

        if($request->all_or_select == 0){

            \DB::table('tenant_notifications')->insert([
                'title' => $request->title,
                'message' => $request->message,
                'tenant_id' => 0,
                'pm_company_id' => 0,
                'property_manager_id' => 0,
                'message_language' => $request->message_language,
                'seen_by' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            //start
           // \Log::info('contolio_admin_topic',$request->title,$request->message,'admin_all_tenant_push_notofication');
<<<<<<< HEAD
           $type = 'noti';
            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_admin_topic',$request->title,$request->message,'admin_all_tenant_push_notofication',$type);
=======
            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_admin_topic'.'_'.$request->message_language,$request->title,$request->message,'admin_all_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316

        }else{
            //\Log::info('running else');

            foreach($request->tenant_id as $tenant_id){

                \DB::table('tenant_notifications')->insert([
                    'title' => $request->title,
                    'message' => $request->message,
                    'tenant_id' => $tenant_id,
                    'pm_company_id' => 0,
                    'property_manager_id' => 0,
                    'message_language' => $request->message_language,
                    'seen_by' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
<<<<<<< HEAD
                $type = 'noti';
            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_'.$tenant_id,$request->title,$request->message,'admin_tenant_push_notofication',$type);
=======
            \App\Services\SendTopicPushNotifica::send_topic_push_noti_android('contolio_'.$tenant_id.'_'.$request->message_language,$request->title,$request->message,'admin_single_tenant_push_noti','noti',0,0,$request->message_language);
>>>>>>> ad279ad371a09d34bced6dbf882c71fe2047e316

            //start
            }//foreach

        }

        return $this->sendResponse( [] , 'Notification sent successfully' ,200,200);
    }



}
