<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\Api\UserRequests;
use Illuminate\Http\Request;
use App\Models\BuildingModel;
use App\Models\Owner;
use App\Helpers\Helper;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AvaliableUnit;
use App\Models\AvailableUnitImageModel;
use App\Models\CountryCurrencyModel;
use App\Models\TenantsUnitModel;
use App\Models\TenantModel;
use App\Models\PropertyManagerCompany;
use Illuminate\Support\Carbon;

class BuildingManageController extends ApiBaseController
{

    public function check_pm_access_token(){
        // \Log::debug('run check_pm_access_token');
        return response()->json( array('success' => true) );
    }

    public function add_building(Request $request){
         $validator = validator($request->all(), UserRequests::add_building_request());
         if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
         }
         $building =  BuildingModel::create(['building_name' => $request->building_name , 'address' =>  $request->address ,
        'location_link' =>  $request->location , 'description' =>  $request->description ,'status' => 1 ,
        'property_manager_id' => $request->user()->id , 'pm_company_id' => $request->user()->pm_company_id , 'building_code' => '' ]);
         $building_code =  Helper::generate_uniq_code($building->id);
         BuildingModel::where('id' , $building->id )->update(['building_code' => 'BL'.$building_code]);
         $building['building_code'] = $building_code;

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('building','create',$building->id,$request->user()->id,$request->user()->pm_company_id,$request->building_name,'building_added');
        //  return $this->sendResponse($building ,'Building added successfully',200,200);
        return $this->sendResponse($building ,__(app()->getLocale().'.building_added'),200,200);
    }


    // tenant_units
    public function delete_tenant_units(Request $request){
        try {
            $validator = validator($request->all(),['unit_id' => 'required|numeric|exists:tenants_units,id']);
            if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }

            $payment = \DB::table('payments')
            ->where('unit_id', $request->unit_id)
            ->value('unit_id');

            $contract = \DB::table('contracts_tables')
            ->where('unit_id', $request->unit_id)
            ->value('unit_id');

            $maintance_requests = \DB::table('maintance_requests')
            ->where('unit_id', $request->unit_id)
            ->value('unit_id');

            if(blank($payment)&&blank($contract)&&blank($maintance_requests)){

                $unit_no = \DB::table('tenants_units')
                ->where('id', $request->unit_id)
                ->value('unit_no');

              //module,action,affected_record_id,pm_id,pm_company_id,record_name
              \App\Services\PmLogService::pm_log_delete_entry('unit','delete',$request->unit_id,$request->user()->id,$request->user()->pm_company_id,$unit_no,'unit_deleted');

                TenantsUnitModel::where('id' , $request->unit_id )->delete();
                // return $this->sendResponse([] ,'Unit has been deleted successfully.',200,200);
                return $this->sendResponse([] ,__(app()->getLocale().'.unit_deleted'),200,200);

            }else{

                // return $this->sendResponse([] ,'Sorry unit can not be deleted at this moment due to payments,contracts,maintance requests related to this unit found.',201,200);
                return $this->sendResponse([] ,__(app()->getLocale().'.unit_cannot_deleted'),201,200);

            }

        } catch (\Throwable $th) {
            // \Log::info($th);
            return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }


    public function get_tenant_list($building_id){
       $tenant_list =  \App\Models\TenantModel::where('building_id', $building_id )->select('id', 'first_name' , 'email')
        ->get();
        return $this->sendResponse($tenant_list ,'get_tenant_list',200,200);
    }


    //available units
    public function edit_available_units(Request $request){
        // \Log::info(json_encode( $request->all()));
        $attributeNames = array(
            'monthly_rent' => __(app()->getLocale().'.monthly_rent'),
         );

        $validator = validator($request->all(), UserRequests::edit_available_units());

        $validator->setAttributeNames($attributeNames);

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $old_unit_no =  AvaliableUnit::where('id', $request->unit_id)->value('unit_no');

        if(blank($request->description)){
            $request->description = '';
        }

        if(blank($request->area)){
            $request->area = 0;
        }

        AvaliableUnit::where('id', $request->unit_id)
        ->update([
            'unit_no' => $request->unit_num,
            'building_id' =>$request->building_id ,
            'address' => '' ,
            'rooms' =>  $request->rooms ,
            'bathrooms' =>$request->bathrooms ,
            'area_sqm'=>$request->area ,
            'monthly_rent' =>$request->monthly_rent,
            'description' => $request->description
        ]);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('available unit','edit',$request->unit_id,$request->user()->id,$request->user()->pm_company_id,$old_unit_no,'available_unit_edit');

        // return $this->sendResponse([] ,'Available unit has been updated successfully.',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.available_unit_updated'),200,200);
    }

    public function get_all_images_by_available_unit_id(Request $request){

        $validator = validator($request->all(), UserRequests::get_all_images_by_available_unit_id());

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $AvailableUnitImage = AvailableUnitImageModel::where('unit_id', $request->available_unit_id )->select('id','image_name')->get();

        return $this->sendResponse($AvailableUnitImage ,'get_all_images_by_available_unit_id',200,200);
    }

    //post
    public function delete_image_of_available_unit(Request $request){

        $validator = validator($request->all(), UserRequests::delete_image_of_available_unit());

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $available_unit_id = \DB::table('available_unit_image')->where('id', $request->available_unit_image_id )->value('unit_id');

        $count = \DB::table('available_unit_image')->where('unit_id', $available_unit_id )->count();
        if($count == 1){
        //    return $this->sendSingleFieldError('Sorry, minimum 1 image should be exists!',201,200);
           return $this->sendSingleFieldError(__(app()->getLocale().'.minimum_one_image'),201,200);

        }

        $image_name = \DB::table('available_unit_image')->where('id', $request->available_unit_image_id )->value('image_name');

        // delete file from storage
        \App\Services\FileUploadService::delete_from_azure($image_name, 'delete_image_of_available_unit');

        \DB::table('available_unit_image')->where('id', $request->available_unit_image_id )->delete();

        // return $this->sendResponse([] ,'Image deleted successfully',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.image_deleted'),200,200);

    }


    //post
    public function add_new_image_to_available_unit(Request $request){

        $validator = validator($request->all(), UserRequests::add_new_image_to_available_unit());

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        if(!$request->hasFile('available_unit_image')){
            // return $this->sendSingleFieldError('available_unit_image is required',201,200);
            return $this->sendSingleFieldError(__(app()->getLocale().'.image_is_required'),201,200);

        }

        $count = \DB::table('available_unit_image')->where('unit_id', $request->available_unit_id )->count();
        if($count >= 5){
        //    return $this->sendSingleFieldError('Sorry, maximum 5 images allowed!',201,200);
           return $this->sendSingleFieldError(__(app()->getLocale().'.max_five_files'),201,200);

        }

        $imageName = uniqid().'.'.$request->file('available_unit_image')->getClientOriginalExtension();

        // upload on storage
        \App\Services\FileUploadService::upload_to_azure($imageName,$request->file('available_unit_image'), 'add_new_image_to_available_unit');

        AvailableUnitImageModel::create(['unit_id' => $request->available_unit_id , 'image_name' => $imageName]);

        // return $this->sendResponse([] ,'New image added successfully.',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.image_added'),200,200);

    }


    //tenant_units
    public function get_tenant_units_list(Request $request){

        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;

        $tenant_units_query = TenantsUnitModel::join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('buildings.pm_company_id', $request->user()->pm_company_id );

            if($request->filter_by_status == 1){
                $tenant_units_query->where('tenants_units.status', 1);
            }elseif($request->filter_by_status == 2){
                $tenant_units_query->where('tenants_units.status', 0);
            }

            $tenant_units = $tenant_units_query->select(
                'tenants_units.id' ,
                'tenants_units.unit_no',
                'tenants_units.unit_code' ,
                'buildings.address' ,
                'tenants_units.tenant_id' ,
                'tenants_units.status',
                'buildings.building_name',
                'tenants_units.maintenance_included'
            )
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function($query){
                $temp_name = \DB::table('tenants')->where('id', $query->tenant_id)->select('first_name','last_name')->first();
                if(!blank($temp_name)){
                    $query->tenant_name = $temp_name->first_name.' '.$temp_name->last_name;
                }else{
                    $query->tenant_name = '';
                }
                return $query;
            });


            if($request->filter_by_status == 0){
                $tenant_units_count = TenantsUnitModel::join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                        ->where('buildings.pm_company_id', $request->user()->pm_company_id )
                        ->count();
            }else{
                if($request->filter_by_status == 1){
                    $tenant_units_count = TenantsUnitModel::join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                        ->where('buildings.pm_company_id', $request->user()->pm_company_id )
                        ->where('tenants_units.status', 1)
                        ->count();
                }elseif($request->filter_by_status == 2){
                    $tenant_units_count = TenantsUnitModel::join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
                        ->where('buildings.pm_company_id', $request->user()->pm_company_id )
                        ->where('tenants_units.status', 0)
                        ->count();
                }

            }

        $response = [
            'success' => true,
            'data'    => $tenant_units,
            'message' => 'get_tenant_units_list',
            'pagecount'  => (int)ceil($tenant_units_count/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    //pm
    //post
    public function get_available_units_listing(Request $request){
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;

        $available_units_query = AvaliableUnit::join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
        ->where('buildings.pm_company_id', $request->user()->pm_company_id );

        if($request->filter_by_status == 1){
            $available_units_query->where('avaliable_units.status', 1);
        }elseif($request->filter_by_status == 2){
            $available_units_query->where('avaliable_units.status', 0);
        }

        $available_units = $available_units_query->select(
            'avaliable_units.id' ,
            'avaliable_units.unit_code' ,
            'avaliable_units.bathrooms',
            'avaliable_units.rooms',
            'avaliable_units.area_sqm',
            'avaliable_units.monthly_rent', 'avaliable_units.unit_no' ,
            'avaliable_units.status',

            'buildings.pm_company_id',
            'buildings.building_name',
            )
        ->take(10)
        ->skip($skip)
        ->get()
        ->transform(function($query) {
            $query->file_image = AvailableUnitImageModel::where('unit_id', $query->id)->value('image_name');

            $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');
            $query->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');
            return $query;
        });


        //count -----------------------------------------
        if($request->filter_by_status == 0){
            $available_units_count = AvaliableUnit::join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            ->where('buildings.pm_company_id', $request->user()->pm_company_id )
            ->count();
        }else{
            if($request->filter_by_status == 1){
                $available_units_count = AvaliableUnit::join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
                ->where('buildings.pm_company_id', $request->user()->pm_company_id )
                ->where('avaliable_units.status', 1)
                ->count();
            }elseif($request->filter_by_status == 2){
                    $available_units_count = AvaliableUnit::join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
                ->where('buildings.pm_company_id', $request->user()->pm_company_id )
                ->where('avaliable_units.status', 0)
                ->count();
            }
        }

        $response = [
            'success' => true,
            'data'    => $available_units,
            'message' => 'get_available_units_listing',
            'pagecount'  => (int)ceil($available_units_count/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    //POST
    //edit unit
    public function edit_tenant_units(Request $request){
        try {

            $attributeNames = array(
                'monthly_rent' => __(app()->getLocale().'.monthly_rent'),
             );

            $validator = validator($request->all(), UserRequests::edit_tenant_units());
            $validator->setAttributeNames($attributeNames);
            if($validator->fails()){
                return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }

            if($request->tenant_id != 0){

                $_tenant_exists = \DB::table('tenants')->where('id', $request->tenant_id)->first();
                if(blank($_tenant_exists)){
                    return $this->sendSingleFieldError('Invalid tenant id',201,200);
                }

                $temp_data = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();

                $tenant_name = $temp_data->first_name.$temp_data->last_name;
                //module,action,affected_record_id,pm_id,pm_company_id   tenant_name or status?
               \App\Services\PmLogService::pm_log_entry('tenant','staus',$request->tenant_id,$request->user()->id,$request->user()->pm_company_id,$tenant_name, 'tenant_unit_edit');

                TenantModel::where('id', $request->tenant_id)->update(['status' => 1 ]);

            }

            if($request->owner_id != 0){

                $_owner_exists = \DB::table('owners')->where('id', $request->owner_id)->first();
                if(blank($_owner_exists)){
                    return $this->sendSingleFieldError('Invalid owner id',201,200);
                }
            }

            //for logs
            $old_unit_no =  TenantsUnitModel::where('id', $request->unit_id)->value('unit_no');

            //update - if tenant changed from edit unit,
            //then tenant_id will be automatically update in maintance_requests table for that unit
            $old_tenant_id = TenantsUnitModel::where('id', $request->unit_id)->value('tenant_id');
            if($request->tenant_id != $old_tenant_id){
                \App\Models\MaintanceRequestModel::where('unit_id', $request->unit_id)->update(['tenant_id' => $request->tenant_id ]);
            }

            if(blank($request->description)){

                $request->description = '';
            }

            if(blank($request->area)){

                $request->area = 0;
            }

            TenantsUnitModel::where('id', $request->unit_id)
            ->update([
                'pm_company_id' => $request->user()->pm_company_id ,
                'unit_no'=>$request->unit_num ,
                'building_id'=> $request->building_id ,
                'owner_id' => $request->owner_id ,
                'tenant_id' => $request->tenant_id ,
                'rooms' => $request->rooms ,
                'bathrooms' => $request->bathrooms,
                'area_sqm' => $request->area,
                'address' => '',
                'monthly_rent' => $request->monthly_rent,
                'status' => 1,
                'description' => $request->description,
                'maintenance_included' => $request->maintenance_included
            ]);

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('unit','edit',$request->unit_id,$request->user()->id,$request->user()->pm_company_id,$old_unit_no, 'tenant_unit_edit');

            // return $this->sendResponse([] ,'Tenant unit has been updated successfully.',200,200);
            return $this->sendResponse([] ,__(app()->getLocale().'.tenant_unit_updated'),200,200);
        } catch (\Throwable $th) {
            // \Log::info($th);
            return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }


    //tenant_units
    public function get_tenant_units_info($unit_id){
        try {
            $validator = validator(['unit_id' => $unit_id] , [
                'unit_id' =>'required|numeric|exists:tenants_units,id'
            ]);
            if($validator->fails()){
               return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
            $units_details = TenantsUnitModel::Join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            // ->leftoin('owners', 'tenants_units.owner_id', '=', 'owners.id')
            // ->leftjoin('tenants' , 'tenants_units.tenant_id' ,  '=', 'tenants.id' )
            ->where('tenants_units.id', $unit_id )
            ->select('tenants_units.id' ,
                     'tenants_units.unit_code' ,
                     'tenants_units.rooms',
                     'tenants_units.address' ,
                     'tenants_units.bathrooms',
                     'tenants_units.area_sqm',
                     'tenants_units.description' ,
                     'tenants_units.monthly_rent',
                     'tenants_units.unit_no' ,
                     'buildings.building_name',
                     'tenants_units.status' ,
                     'tenants_units.tenant_id',
                     'tenants_units.maintenance_included',
                    //  'owners.name as owner_name',
                     'tenants_units.owner_id' ,
                     'tenants_units.building_id' ,
                     'buildings.address as building_address' ,
                     'buildings.location_link',
                     'buildings.pm_company_id',
                    //  'tenants.first_name as tenant_first_name',
                    //  'tenants.id as tenant_id',
                    //  'tenants.last_name as tenant_last_name',
                    //  'tenants.email as tenant_email',
                    //  'tenants.phone  as tenant_phone',
                    //  'tenants.country_code',
                    //  'tenants.country_id',
                     )
                     ->get()
                     ->transform(function ($query){
                        $_temp_owner = \DB::table('owners')->where('id', $query->owner_id)->value('name');
                        $query->owner_name = !blank($_temp_owner) ? $_temp_owner : '';

                        if(blank($_temp_owner)){
                            $query->owner_id = 0;
                        }

                        $_temp_tenant = \DB::table('tenants')->where('id', $query->tenant_id)->first();

                        $query->tenant_id = !blank($_temp_tenant)?$_temp_tenant->id:'';

                        $query->tenant_first_name = !blank($_temp_tenant)?$_temp_tenant->first_name:'';
                        $query->tenant_last_name = !blank($_temp_tenant)?$_temp_tenant->last_name:'';
                        $query->tenant_email = !blank($_temp_tenant)?$_temp_tenant->email:'';
                        $query->tenant_phone = !blank($_temp_tenant)?$_temp_tenant->phone:'';

                        $query->country_code = !blank($_temp_tenant)?$_temp_tenant->country_code:'';

                        $query->country_id = !blank($_temp_tenant)?$_temp_tenant->country_id:'';

                        $_temp_country = \App\models\Country::where('id', $query->country_id)->value('country');

                        $query->tenant_country = !blank($_temp_country) ? $_temp_country :'';

                        $query->address =  $query->building_address;

                        $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');
                        $currency_symbol = \DB::table('currencies')->where('id', $currency_id)->select('currency','symbol')->first();
                        $query->currency = $currency_symbol->currency;
                        $query->symbol = $currency_symbol->symbol;
                        $payment_type = \DB::table('payments')->where('unit_id', $query->id)->value('payment_type');
                        $query->payment = $payment_type == 1 ? 'cheque' : 'manual';
                      return $query;
             })->first();
            return $this->sendResponse($units_details ,'Tenant unit details',200,200);
        } catch (\Throwable $th) {
           \Log::error($th);
           return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }


    public function view_available_unit($available_unit_id){

        $validator = validator(['avaliable_unit_id' => $available_unit_id],
            ['avaliable_unit_id' => 'required|numeric|exists:avaliable_units,id']);

        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $units_details = AvaliableUnit::join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            ->where('avaliable_units.id', $available_unit_id )

            ->select('avaliable_units.id' ,
                'avaliable_units.unit_no' ,
                'avaliable_units.unit_code' ,
                'avaliable_units.status' ,
                'avaliable_units.rooms',
                'avaliable_units.monthly_rent',
                'avaliable_units.bathrooms',
                'avaliable_units.area_sqm',
                'avaliable_units.address as avaliable_units_address',
                'buildings.building_name',
                'buildings.address as building_address',
                'buildings.location_link as building_location_link',
                'avaliable_units.description as building_description' ,
                'buildings.pm_company_id',
                'buildings.id as building_id',
            )
            ->get()
            ->transform(function ($query) use($available_unit_id){

                $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');
                $query->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

                $query->file_images = AvailableUnitImageModel::where('unit_id', $available_unit_id )->select('id','image_name')
                ->get();
                return $query;
            });
        return $this->sendResponse($units_details ,'view_available_unit',200,200);
    }


    public function get_all_buildings(Request $request){
        $pm_listing = BuildingModel::where('pm_company_id' , $request->user()->pm_company_id)
            ->select('building_name', 'id')
            ->where('status',1)
            ->get();
        return $this->sendResponse($pm_listing ,'Building listing',200,200);
    }

    //get
    // add new unit screen
    public function get_currency_code_by_building_id($building_id){
        $validator = validator(['building_id' => $building_id], UserRequests::get_currency_code_by_building_id());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $pm_company_id = BuildingModel::where('id', $building_id)->value('pm_company_id');
        $currency_id = \DB::table('property_manager_companies')->where('id', $pm_company_id)->value('currency_id');
        $currency = \DB::table('currencies')->where('id', $currency_id)->value('currency');
        return $this->sendResponse($currency ,'get_currency_code_by_building_id',200,200);
    }

    public function add_owner(Request $request){

        $validator = validator($request->all(), UserRequests::add_owner_request());
         if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
         }
         $pm_company_id = \DB::table('property_managers')->where('id', $request->user()->id)->value('pm_company_id');

         $owner = Owner::create([
            'name' =>$request->name ,
            'phone' =>$request->phone ,
            'email' => $request->email,
            'remarks'=> (!blank($request->remarks))?$request->remarks:'' ,
            'owner_code' => '' ,
            'status' => '1' ,
            'pm_company_id' => $pm_company_id,
            'property_manager_id' => $request->user()->id

        ]);

         $owner_code =  Helper::generate_uniq_code($owner->id);
         Owner::where('id' , $owner->id)->update(['owner_code' => 'OW'.$owner_code]);
         $owner['owner_code'] = $owner_code ;

         //module,action,affected_record_id,pm_id,pm_company_id
         \App\Services\PmLogService::pm_log_entry('owner','create',$owner->id,$request->user()->id,$request->user()->pm_company_id,$request->name ,'owner_added');

        //  return $this->sendResponse($owner ,'Owner added successfully',200,200);
         return $this->sendResponse($owner,__(app()->getLocale().'.owner_added'),200,200);

    }

    //avail unit
    public function add_units(Request $request){
        // \Log::info($request->all());
        $attributeNames = array(
            'monthly_rent' => __(app()->getLocale().'.monthly_rent'),
         );

        $validator = validator($request->all(), UserRequests::add_units_request());

        $validator->setAttributeNames($attributeNames);

        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        //\Log::info(json_encode($request->all()));

        #currency id will be based on company
        // $currency_id = \DB::table('property_manager_companies')->where('id', $request->user()->pm_company_id)->value('currency_id');

        if(blank($request->description)){

            $request->description = '';
        }

        if(blank($request->area_sqm)){

            $request->area_sqm = 0;
        }

        $unit =  AvaliableUnit::create([
            'pm_company_id' => $request->user()->pm_company_id ,
            'unit_no'=>$request->unit_num ,
            'building_id'=> $request->building_id ,
            'address' => '' ,
            'rooms' => $request->rooms ,
            'bathrooms' => $request->bathrooms,
            'area_sqm' => $request->area_sqm ,
            'unit_code' => '',
            'monthly_rent' => $request->monthly_rent,
            'status' => 1,
            'description' => $request->description
        ]);
        $files = $request->file('units_file');
        $unit_code =  Helper::generate_uniq_code($unit->id);
        AvaliableUnit::where('id', $unit->id)->update(['unit_code' => 'AU'.$unit_code ]);
        foreach($files as $index=>$file)
        {
            unset($imageName);
            $imageName = uniqid().$file->getSize().$index.'.'.$file->getClientOriginalExtension();
            \Storage::disk('azure')->put($imageName, \File::get($file));
            AvailableUnitImageModel::create(['unit_id' => $unit->id , 'image_name' => $imageName]);
        }

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('avaliable unit','create',$unit->id,$request->user()->id,$request->user()->pm_company_id,$request->unit_num, 'unit_added');

        // return $this->sendResponse([] ,'Unit has been added successfully.',200,200)
        return $this->sendResponse([] ,__(app()->getLocale().'.unit_added'),200,200);
    }


    //get
    //tenant_units
    public function owners_dropdown_by_pm(Request $request){
        $Owner = Owner::where('pm_company_id', $request->user()->pm_company_id)->select( 'name', 'id')->get();
        return $this->sendResponse($Owner,'owners_dropdown_by_pm',200,200);
    }

    //get
    //tenant_units
    public function tenants_dropdown_by_pm_company_id(Request $request){
        $tenant_list =  \App\Models\TenantModel::where('pm_company_id', $request->user()->pm_company_id )
            ->select('id', 'first_name' , 'last_name')
            ->get()
            ->transform(function($item)  {
                $item->name =  mb_strimwidth( $item->first_name.' '.$item->last_name, 0, 25, '..');
                unset($item->first_name);
                unset($item->last_name);
                return $item;
            });

        return $this->sendResponse($tenant_list,'tenants_dropdown_by_pm_company_id',200,200);
    }

    //tenant_units
    public function add_tenant_units(Request $request){

        $attributeNames = array(
            'monthly_rent' => __(app()->getLocale().'.monthly_rent'),
         );

       if($request->tenant_id == 0){
            $validator = validator($request->all(), UserRequests::add_tenant_units_without_tenant());
            $validator->setAttributeNames($attributeNames);
            if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
       }else{
            $validator = validator($request->all(), UserRequests::add_tenant_units());
            $validator->setAttributeNames($attributeNames);
            if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }
       }

       if($request->tenant_id != 0){

        $temp_data = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();

        $tenant_name = $temp_data->first_name.$temp_data->last_name;
        //module,action,affected_record_id,pm_id,pm_company_id   tenant_name or status?
       \App\Services\PmLogService::pm_log_entry('tenant','staus',$request->tenant_id,$request->user()->id,$request->user()->pm_company_id,$tenant_name, 'tenant_unit_edit');

        TenantModel::where('id', $request->tenant_id)->update(['status' => 1 ]);

       }

       if(blank($request->description)){

        $request->description = '';
    }

    if(blank($request->area)){

        $request->area = 0;
    }

        $unit =  TenantsUnitModel::create([
            'pm_company_id' => $request->user()->pm_company_id ,
            'unit_no'=>$request->unit_num ,
            'building_id'=> $request->building_id ,
            'owner_id' => $request->owner_id ,
            'tenant_id' => $request->tenant_id ,
            'rooms' => $request->rooms ,
            'bathrooms' => $request->bathrooms,
            'area_sqm' => $request->area,
            'unit_code' => '',
            'address' => '',
            'monthly_rent' => $request->monthly_rent,
            'status' => 1,
            'description' => $request->description,
            'maintenance_included' => $request->maintenance_included
        ]);
        // $files = $request->file('units_file');
        $unit_code =  Helper::generate_uniq_code($unit->id);
        TenantsUnitModel::where('id', $unit->id)->update(['unit_code' => 'TU'.$unit_code ]);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('tenant unit','create',$unit->id,$request->user()->id,$request->user()->pm_company_id,$request->unit_num, 'tenant_unit_added');

        // return $this->sendResponse([] ,'Tenant Unit added successfully.',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.tenant_unit_added'),200,200);

    }


    public function get_all_currency(){
        $currency= \App\Models\Currency::select('id','currency')->get();
        return $this->sendResponse($currency,'currency listing',200,200);
    }

    //18-01-2022
    // client feedback - PM sees all owners in the database, should only see owners related to PM user PM company
    // then when PM user opens owners module, he should only see the Owners related to his company
    public function owner_listing(Request $request){
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;

        if($request->filter_by_status == 0){
            $owner_listing = Owner::where('pm_company_id', $request->user()->pm_company_id)
            ->select('id','name', 'owner_code' , 'email' , 'phone' , 'status')
            ->take(10)
            ->skip($skip)
            ->get();
            $count_owner = Owner::where('pm_company_id', $request->user()->pm_company_id)->count();
        }else{
            if($request->filter_by_status == 1){
                $owner_listing = Owner::where('pm_company_id', $request->user()->pm_company_id)
                ->where('status', 1)
                ->select('id','name', 'owner_code' , 'email' , 'phone' , 'status')
                ->take(10)
                ->skip($skip)
                ->get();
                $count_owner = Owner::where('pm_company_id', $request->user()->pm_company_id)->count();

            }elseif($request->filter_by_status == 2){
                $owner_listing = Owner::where('pm_company_id', $request->user()->pm_company_id)
                ->where('status', 0)
                ->select('id','name', 'owner_code' , 'email' , 'phone' , 'status')
                ->take(10)
                ->skip($skip)
                ->get();
                $count_owner = Owner::where('pm_company_id', $request->user()->pm_company_id)->count();
            }
        }


        $response = [
            'success' => true,
            'data'    => $owner_listing,
            'message' => 'Building list',
            'pagecount'  => (int)ceil($count_owner/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }

    public function get_all_owners(){
        $owner_listing = Owner::select('id','name')
        ->get();
        return $this->sendResponse($owner_listing ,'Owner list.',200,200);
    }

    public function update_building(Request $request){
        $validator = validator($request->all(), UserRequests::update_building_request());
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $old_building_name = BuildingModel::where('id' , $request->building_id )->value('building_name');

        $building =  BuildingModel::where('id' , $request->building_id )
        ->update(['building_name' => $request->building_name , 'address' =>  $request->address ,
        'location_link' =>  $request->location , 'description' =>  $request->description]);

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('building','edit',$request->building_id,$request->user()->id,$request->user()->pm_company_id,$old_building_name, 'building_edit');

        // return $this->sendResponse([] ,'Building has been updated successfully.',200,200);
        return $this->sendResponse([] ,__(app()->getLocale().'.building_updated'),200,200);

    }


    //pm
    //post
    public function building_listing(Request $request){
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;


        $building_listing_query = BuildingModel::where('pm_company_id' , $request->user()->pm_company_id );

        if($request->filter_by_status == 1){
            $building_listing_query->where('status', 1);
        }elseif($request->filter_by_status == 2){
            $building_listing_query->where('status', 0);
        }

        $building_listing = $building_listing_query->select('building_name', 'address' , 'location_link' , 'units' , 'id' , 'status')
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function($item) {
                $item->units = \DB::table('tenants_units')->where('building_id', $item->id)->count();
                return $item;
            });


        // count ---------------------------------------
        if($request->filter_by_status == 0){
            $count_building = BuildingModel::where('pm_company_id' , $request->user()->pm_company_id )->count();
        }else{
            if($request->filter_by_status == 1){
                $count_building = BuildingModel::where('pm_company_id' , $request->user()->pm_company_id )
                ->where('status', 1)
                ->count();
            }elseif($request->filter_by_status == 2){
                $count_building = BuildingModel::where('pm_company_id' , $request->user()->pm_company_id )
                ->where('status', 0)
                ->count();
            }
        }

        $response = [
            'success' => true,
            'data'    => $building_listing,
            'message' => 'Building list',
            'pagecount'  => (int)ceil($count_building/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    public function activate_deactivate_building(Request $request){
        $validator = validator($request->all(), ['building_id' => 'required|numeric|exists:buildings,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $building =  BuildingModel::where('id' , $request->building_id )->value('status');

        $all_tenant_units = \DB::table('tenants_units')->select('id')->where('building_id' , $request->building_id )->get();
        $all_avail_units = \DB::table('avaliable_units')->select('id')->where('building_id' , $request->building_id )->get();

        if($building == '1'){
            BuildingModel::where('id' , $request->building_id )->update(['status' => 0]);

            foreach($all_tenant_units as $val){
                \DB::table('tenants_units')->where('id' , $val->id)->update(['status' => 0]);
            }

            foreach($all_avail_units as $val_two){
                \DB::table('avaliable_units')->where('id' , $val_two->id)->update(['status' => 0]);
            }
        }else{
            BuildingModel::where('id' , $request->building_id )->update(['status' => 1]);

            foreach($all_tenant_units as $val){
                \DB::table('tenants_units')->where('id' , $val->id)->update(['status' => 1]);
            }

            foreach($all_avail_units as $val_two){
                \DB::table('avaliable_units')->where('id' , $val_two->id)->update(['status' => 1]);
            }
        }

        // get building name for record name cloum.
        $building_name = BuildingModel::where('id' , $request->building_id )
        ->value('building_name');

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('building','status',$request->building_id,$request->user()->id,$request->user()->pm_company_id,$building_name, 'building_status');

        // return $this->sendResponse( [] ,'Status has been updated successfully.',200,200);
        return $this->sendResponse( [] ,__(app()->getLocale().'.status_updated'),200,200);
    }


    public function activate_deactivate_tenant_units(Request $request){
        $validator = validator($request->all(), ['unit_id' => 'required|numeric|exists:tenants_units,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $units =  TenantsUnitModel::where('id' , $request->unit_id )->value('status');
        if($units == '1'){
            TenantsUnitModel::where('id' , $request->unit_id )
            ->update(['status' => 0]);
        }else{
            TenantsUnitModel::where('id' , $request->unit_id )
            ->update(['status' => 1]);
        }

        // get unit no.
        $unit_no = TenantsUnitModel::where('id' , $request->unit_id )
        ->value('unit_no');

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('tenant unit','status',$request->unit_id,$request->user()->id,$request->user()->pm_company_id,$unit_no, 'tenant_unit_status');

        // return $this->sendResponse( [] ,'Status has been updated successfully.',200,200);
        return $this->sendResponse( [] ,__(app()->getLocale().'.status_updated'),200,200);

    }

    public function activate_deactivate_units(Request $request){
        $validator = validator($request->all(), ['unit_id' => 'required|numeric|exists:avaliable_units,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $units =  AvaliableUnit::where('id' , $request->unit_id )->value('status');
        if($units == '1'){
            AvaliableUnit::where('id' , $request->unit_id )
            ->update(['status' => 0]);
        }else{
            AvaliableUnit::where('id' , $request->unit_id )
            ->update(['status' => 1]);
        }

        // get unit no.
        $unit_no = AvaliableUnit::where('id' , $request->unit_id )
        ->value('unit_no');

       //module,action,affected_record_id,pm_id,pm_company_id
       \App\Services\PmLogService::pm_log_entry('available unit','status',$request->unit_id,$request->user()->id,$request->user()->pm_company_id,$unit_no, 'available_unit_status');

        // return $this->sendResponse( [] ,'Status has been updated successfully.',200,200);
        return $this->sendResponse( [] ,__(app()->getLocale().'.status_updated'),200,200);

    }


    public function view_building($id){
        $validator = validator(['id' => $id], ['id' => 'required|numeric|exists:buildings,id']);
        if($validator->fails()){
           return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $building =  BuildingModel::where('buildings.id' , $id )
        ->leftJoin('property_manager_companies', 'buildings.pm_company_id', '=', 'property_manager_companies.id')
        ->select('buildings.building_name', 'buildings.address' , 'buildings.building_code','buildings.location_link' ,
         'buildings.units' , 'buildings.id' , 'buildings.status', 'buildings.description','property_manager_companies.name' )
         ->first();
        //  return $this->sendResponse(  $building  ,'Status has been updated successfully.',200,200);
         return $this->sendResponse( $building ,'view_building',200,200);

    }

    public function export_the_listing(){
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    //pm
    //post
    public function search_available_units(Request $request){
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;
        $search_key = $request->search_key;

        $available_units_search_result_query = AvaliableUnit::Join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
        ->where('buildings.pm_company_id', $request->user()->pm_company_id )

        ->where(function ($query) use( $search_key ) {
            $query->where('avaliable_units.unit_code' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.unit_no' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.rooms' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.bathrooms' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.area_sqm' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.monthly_rent' , 'LIKE' , "%$search_key%" )

                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%" );
        });

        if($request->filter_by_status == 1){
            $available_units_search_result_query->where('avaliable_units.status', 1);
        }elseif($request->filter_by_status == 2){
            $available_units_search_result_query->where('avaliable_units.status', 0);
        }

        $available_units_search_result = $available_units_search_result_query->select(
            'avaliable_units.id' ,
            'avaliable_units.unit_code' ,
            'avaliable_units.bathrooms',
            'avaliable_units.rooms',
            'avaliable_units.address' ,
            'avaliable_units.area_sqm',
            'avaliable_units.description' ,
            'avaliable_units.monthly_rent',
            'avaliable_units.unit_no' ,
            'avaliable_units.status',

            'buildings.building_name',
            'buildings.pm_company_id',
        )

        ->take(10)
        ->skip($skip)
        ->get()
        ->transform(function($query){
            $query->file_image = AvailableUnitImageModel::where('unit_id', $query->id)->value('image_name');
            $currency_id = \DB::table('property_manager_companies')->where('id', $query->pm_company_id)->value('currency_id');
            $query->currency_symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

            return $query;
        });


        //count -------------------------------------------------------------
        $available_units_search_result_count_query =  AvaliableUnit::Join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            ->where('buildings.pm_company_id', $request->user()->pm_company_id );

        if($request->filter_by_status == 1){
            $available_units_search_result_count_query->where('avaliable_units.status', 1);
        }elseif($request->filter_by_status == 2){
            $available_units_search_result_count_query->where('avaliable_units.status', 0);
        }

        $available_units_search_result_count = $available_units_search_result_count_query
        ->where(function ($query) use( $search_key ) {
            $query->where('avaliable_units.unit_code' , 'LIKE' , "%$search_key%" )
            ->orWhere('avaliable_units.unit_no' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.rooms' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.bathrooms' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.area_sqm' , 'LIKE' , "%$search_key%" )
                ->orWhere('avaliable_units.monthly_rent' , 'LIKE' , "%$search_key%" )

                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%" );
        })
        ->count();

        $response = [
            'success' => true,
            'data'    => $available_units_search_result ,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($available_units_search_result_count/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    // tenant_units
    public function search_all_units(Request $request){
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;
        $search_key = $request->search_key;

        $units_search_result_query = TenantsUnitModel::Join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
        ->leftjoin('tenants', 'tenants_units.tenant_id', '=', 'tenants.id')
        ->where('buildings.pm_company_id', $request->user()->pm_company_id)

        ->where(function ($query) use($search_key){

            $query->where('tenants_units.unit_code' , 'LIKE' , "%$search_key%" )
                ->orWhere('tenants_units.unit_no' , 'LIKE' , "%$search_key%" )
                ->orWhere('buildings.address' , 'LIKE' , "%$search_key%" )
                ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                ->orWhere('tenants.first_name', 'LIKE', "%$search_key%" )
                ->orWhere('tenants.last_name', 'LIKE', "%$search_key%" );
        });

        if($request->filter_by_status == 1){
            $units_search_result_query->where('tenants_units.status', 1);
        }elseif($request->filter_by_status == 2){
            $units_search_result_query->where('tenants_units.status', 0);
        }

        $units_search_result = $units_search_result_query->select(
            'tenants_units.id',
            'tenants_units.unit_code',
            'tenants_units.unit_no',
            'buildings.building_name',
            'tenants.first_name',
            'tenants.last_name',
            'buildings.address',
            'tenants_units.status',
            )

        ->take(10)
        ->skip($skip)
        ->get()
        ->transform(function($query){
            $query->tenant_name = $query->first_name.' '.$query->last_name;
            return $query;
        });


        //count -------------------------------
        $units_search_result_count_query =  TenantsUnitModel::Join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->leftjoin('tenants', 'tenants_units.tenant_id', '=', 'tenants.id')
            ->where('buildings.pm_company_id', $request->user()->pm_company_id )
            ->where(function ($query) use( $search_key ){

                $query->where('tenants_units.unit_code' , 'LIKE' , "%$search_key%" )
                    ->orWhere('tenants_units.unit_no' , 'LIKE' , "%$search_key%" )
                    ->orWhere('buildings.address' , 'LIKE' , "%$search_key%" )
                    ->orWhere('buildings.building_name', 'LIKE', "%$search_key%")
                    ->orWhere('tenants.first_name', 'LIKE', "%$search_key%" )
                    ->orWhere('tenants.last_name', 'LIKE', "%$search_key%" );
            });

        if($request->filter_by_status == 1){
            $units_search_result_count_query->where('tenants_units.status', 1);
        }elseif($request->filter_by_status == 2){
            $units_search_result_count_query->where('tenants_units.status', 0);
        }

        $units_search_result_count = $units_search_result_count_query->count();

        $response = [
            'success' => true,
            'data'    => $units_search_result ,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($units_search_result_count/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }




    public function edit_owner(Request $request){

        $validator = validator($request->all() , UserRequests::edit_owner_request());
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }


        $old_owner = Owner::where('id', $request->owner_id)->value('name');

        if($request->has('remarks')){
            if(!blank($request->remarks)){
                $remarks = $request->remarks;
            }else{
                $remarks = '';
            }
        }else{
            $remarks = '';
        }

        Owner::where('id', $request->owner_id)->update([
            'name' =>$request->name ,
            'email' =>$request->email ,
            'phone' =>$request->phone ,
            'remarks' =>$remarks
        ]);

        //module,action,affected_record_id,pm_id,pm_company_id
       \App\Services\PmLogService::pm_log_entry('owner','edit',$request->owner_id,$request->user()->id,$request->user()->pm_company_id,$old_owner, 'owner_edit');

        // return $this->sendResponse( [] ,'Owner has been updated successfully.',200,200);
        return $this->sendResponse( [] ,__(app()->getLocale().'.owner_updated'),200,200);

    }

    //get
    public function owner_details($owner_id){
        $validator = validator(['owner_id' => $owner_id], ['owner_id' => 'required|numeric|exists:owners,id']);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }
       $owner = Owner::where('id',$owner_id )->first();
       $owner->company_name = \DB::table('property_manager_companies')->where('id', $owner->pm_company_id)->value('name');
    //    $owner->property_manager_name = \DB::table('property_managers')->where('id', $owner->property_manager_id)->value('name');

       return $this->sendResponse( $owner ,'Owner details.',200,200);
    }

    //get
    public function delete_owner($owner_id,Request $request){
        $validator = validator(['owner_id' => $owner_id], ['owner_id' => 'required|numeric|exists:owners,id']);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $owner = \DB::table('tenants_units')
        ->where('owner_id', $owner_id)
        ->value('owner_id');

        if(blank($owner)){

            $owner_name = \DB::table('owners')
             ->where('id', $owner)
             ->value('name');

            //module,action,affected_record_id,pm_id,pm_company_id,record_name
            \App\Services\PmLogService::pm_log_delete_entry('owner','delete',$owner_id,$request->user()->id,$request->user()->pm_company_id,$owner_name,'owner_deleted');

            Owner::where('id',$owner_id )->delete();
            // return $this->sendResponse( [] ,'Owner deleted successfully',200,200);
            return $this->sendResponse( [] ,__(app()->getLocale().'.owner_deleted'),200,200);

        }else{
            // return $this->sendResponse([] ,'Sorry owner can not be deleted at this moment due to units related to this owner found.',201,200);
            return $this->sendResponse([] ,__(app()->getLocale().'.owner_cannot_deleted'),201,200);

        }

    }

    public function search_owner_list(Request $request){
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;
        $search_key = $request->search_key;

        $owner_list_query = Owner::where('pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use( $search_key ) {
                $query->where('name', 'LIKE' , "%$search_key%")
                    ->orWhere('owner_code', 'LIKE', "%$search_key%" )
                    ->orWhere('email', 'LIKE', "%$search_key%" )
                    ->orWhere('phone', 'LIKE', "%$search_key%" );
            });

        if($request->filter_by_status == 1){
            $owner_list_query->where('status', 1);
        }elseif($request->filter_by_status == 2){
            $owner_list_query->where('status', 0);
        }

        $owner_list = $owner_list_query->select('id','name', 'owner_code' , 'email' , 'phone' , 'remarks' , 'status')
            ->take(10)
            ->skip($skip)
            ->get();



        //count -----------------
        $owner_list_count_query = Owner::where('pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use( $search_key ) {
                $query->where('name', 'LIKE' , "%$search_key%")
                    ->orWhere('owner_code', 'LIKE', "%$search_key%" )
                    ->orWhere('email', 'LIKE', "%$search_key%" )
                    ->orWhere('phone', 'LIKE', "%$search_key%" );
            });

        if($request->filter_by_status == 1){
            $owner_list_count_query->where('status', 1);
        }elseif($request->filter_by_status == 2){
            $owner_list_count_query->where('status', 0);
        }

        $owner_list_count = $owner_list_count_query->count();

        $response = [
            'success' => true,
            'data'    =>  $owner_list,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($owner_list_count/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    //pm
    //post
    public function search_building(Request $request){
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'filter_by_status' => 'required|in:0,1,2',//0 all, 1 active, 2 inactive
        ]);
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $page = $request->page;
        $skip = $page?10 * ($page - 1):0;
        $search_key = $request->search_key;

        $building_query =  BuildingModel::where('pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use( $search_key ) {
                $query->where('building_name', 'LIKE', "%$search_key%")
                    ->orWhere('address', 'LIKE', "%$search_key%" );
            });

        if($request->filter_by_status == 1){
            $building_query->where('status', 1);
        }elseif($request->filter_by_status == 2){
            $building_query->where('status', 0);
        }

        $building = $building_query->select('building_name', 'address' , 'location_link' , 'units' , 'id' , 'status')
            ->take(10)
            ->skip($skip)
            ->get()
            ->transform(function($item) {
                $item->units = \DB::table('tenants_units')->where('building_id', $item->id)->count();
                return $item;
            });


        // count -----------------------------------
        $count_building_query =  BuildingModel::where('pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use( $search_key ) {
                $query->where('building_name', 'LIKE', "%$search_key%")
                    ->orWhere('address', 'LIKE', "%$search_key%" );
            });

        if($request->filter_by_status == 1){
            $count_building_query->where('status', 1);
        }elseif($request->filter_by_status == 2){
            $count_building_query->where('status', 0);
        }

        $count_building = $count_building_query->count();


        $response = [
            'success' => true,
            'data'    => $building,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($count_building/10),
            'status'  => 200
        ];
        return response()->json($response,200);
    }


    public function add_contracts(Request $request){
        try {
            $validator = validator($request->all(),  UserRequests::add_contract_request());
            if($validator->fails()){
                return $this->sendSingleFieldError($validator->errors()->first(),201,200);
            }

            if(Carbon::parse($request->expiry_date) < Carbon::parse($request->start_date)){
                // return $this->sendSingleFieldError('Expiry date must be greater than start date',201,200);
                return $this->sendSingleFieldError(__(app()->getLocale().'.expiry_date_greater_than_start_date'),201,200);

            }

            //If expiry date less than today the n expired
            //If expiry date greater the. Today then active
            $status = (Carbon::parse($request->expiry_date) < Carbon::now())?0:1;
            $contract = \App\models\Contract::create(['name'=>$request->contract_name ,
                                                'pm_company_id'=> $request->user()->pm_company_id ,
                                                'Tenant_id' => $request->tenant_id ,
                                                'building_id' => $request->building_id ,
                                                'unit_id' => $request->unit_id ,
                                                'start_date' => Carbon::create($request->start_date) ,
                                                'end_date' =>  Carbon::create($request->expiry_date),
                                                'status' => $status// But we need active/expired auto status

            ]);
            $files = $request->file('contract_media');
            foreach($files as $index=>$file)
            {
                unset($filename);
                $filename = uniqid().$file->getSize().$index.'.'.$file->getClientOriginalExtension();
                \Storage::disk('azure_documents')->put($filename, \File::get($file));
                \App\models\ContractFilesModel::create(['contract_id' => $contract->id , 'file_name' => $filename]);
            }

            //module,action,affected_record_id,pm_id,pm_company_id
            \App\Services\PmLogService::pm_log_entry('contract','create',$contract->id,$request->user()->id,$request->user()->pm_company_id,$request->contract_name, 'contract_added');

            // return $this->sendResponse([] ,'Contract has been added successfully.',200,200);
            return $this->sendResponse([] ,__(app()->getLocale().'.contract_added'),200,200);

        } catch (\Throwable $th) {
            // \Log::info($th);
            return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }

    // get
    //for add_contracts page
    public function building_dropdown_by_company_id(Request $request){
        $Buildings = BuildingModel::where('pm_company_id', $request->user()->pm_company_id )
            ->select('id', 'building_name')->get();
        return $this->sendResponse( $Buildings ,'building_dropdown_by_company_id' , 200 , 200);
    }

    // post
    //for add_contracts page
    public function tenant_unit_dropdown_by_building_id(Request $request){
        $validator = validator($request->all(), ['building_id' =>     'required|numeric|exists:buildings,id'] );
        if($validator->fails()){
            return $this->sendSingleFieldError($validator->errors()->first(),201,200);
        }

        $TenantsUnits = TenantsUnitModel::where('building_id', $request->building_id )
            ->select('id', 'unit_no' , 'maintenance_included')->get();
        return $this->sendResponse( $TenantsUnits ,'tenant_unit_dropdown_by_building_id' , 200 , 200);
    }

    public function get_tenant_by_unit_id($unit_id){
        try {
            $get_tenant_list =  TenantsUnitModel::leftJoin('tenants', 'tenants_units.tenant_id', '=', 'tenants.id')
            ->where('tenants_units.id', $unit_id)
            ->select('tenants.first_name' , 'tenants.last_name', 'tenants.id as tenant_id')
            ->first();

            if(!blank($get_tenant_list->first_name)){

                $get_tenant_list->name =  mb_strimwidth( $get_tenant_list->first_name.' '.$get_tenant_list->last_name, 0, 25, '..');

            }else{

                $get_tenant_list->name= '';

            }
            unset($get_tenant_list->first_name);
            unset($get_tenant_list->last_name);

            return $this->sendResponse( $get_tenant_list ,'tenant listing' , 200 , 200);
        } catch (\Throwable $th) {
            // \Log::info($th);
            return $this->sendSingleFieldError('There is some error in this api',201,200);
        }
    }



}
