<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\PmRequest;
use Illuminate\Http\Request;
use App\Models\ExpertModel;
use App\Models\SpecialtiesModel;
use App\Models\MaintanceExpertModel;
use App\Models\SpecialisteExpertId;
use Hamcrest\Arrays\IsArray;

class ExpertController extends ApiBaseController
{
    // get
    public function specialties_dropdown(Request $request)
    {
        $lang = app()->getLocale();

        if($lang == 'en'){

            $specialties = SpecialtiesModel::select('name', 'id')->get();
        }
        elseif($lang == 'ar'){

            $specialties = SpecialtiesModel::select('arabic_name as name', 'id')->get();

        }
        return $this->sendResponse($specialties, 'specialties_dropdown', 200, 200);
    }

    // post
    public function add_experts(Request $request)
    {

        $validator = validator($request->all(), PmRequest::add_experts());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expert = ExpertModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'remark' => $request->remark,
            'country_code' => $request->country_code,
            'pm_company_id' => $request->user()->pm_company_id,
        ]);

        foreach ($request->speciality_id as $key => $insert) {

            //$expert_id = ExpertModel::where('phone', $request->phone)->value('id');

            $specialistecpertid = SpecialisteExpertId::create([

                'expert_id' => $expert->id,
                'speciality_id' => $request->speciality_id[$key],

            ]);
        }

        //module,action,affected_record_id,pm_id,pm_company_id
       \App\Services\PmLogService::pm_log_entry('expert','create',$expert->id,$request->user()->id,$request->user()->pm_company_id,$request->name, 'expert_added');

        // return $this->sendResponse($expert, 'Expert added successfully', 200, 200);
        return $this->sendResponse($expert,__(app()->getLocale().'.expert_added'), 200, 200);


    }



    //  POST
    //PM
    //speciality_id  0 all
    public function experts_list_by_company_id(Request $request)
    {
        $validator = validator($request->all(), [
            'page' => 'required|numeric',
            'speciality_id' => 'required|numeric', //0 all
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ( ($request->speciality_id != 0) ) {
            $validator = validator($request->all(), [
                'speciality_id' => 'exists:specialities,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;

        \DB::statement("SET SQL_MODE=''");
        $search_expert_query = \DB::table('experts')
            ->leftJoin('specialisties_expert_id', 'experts.id', '=', 'specialisties_expert_id.expert_id')
            ->leftJoin('specialities', 'specialisties_expert_id.speciality_id', '=', 'specialities.id')
            ->where('experts.pm_company_id', $request->user()->pm_company_id);

            if($request->speciality_id != 0){
                $search_expert_query->where('specialities.id', $request->speciality_id);
            }

            $expert_details = $search_expert_query->select(
                'experts.id',
                'experts.name',
                'experts.country_code',
                'experts.phone',
                'experts.email',
                'specialities.name as speciality_name',
            )
            ->take(10)
            ->skip($skip)
            ->groupBy('experts.id')
            ->get()
            ->transform(function ($item) {
                $item->phone = '+'.$item->country_code.' '.$item->phone;
                return $item;
            });

        // \DB::enableQueryLog();

        $search_expert_count_query = \DB::table('experts')
            ->leftJoin('specialisties_expert_id', 'experts.id', '=', 'specialisties_expert_id.expert_id')
            ->leftJoin('specialities', 'specialisties_expert_id.speciality_id', '=', 'specialities.id')
            ->where('experts.pm_company_id', $request->user()->pm_company_id);

        if($request->speciality_id != 0){
            $search_expert_count_query->where('specialities.id', $request->speciality_id);
        }

        $expert_details_count = $search_expert_count_query
        ->distinct('experts.id')
        ->count();

        $response = [
            'success' => true,
            'data'    => $expert_details,
            'message' => 'experts_list_by_company_id',
            'pagecount'  => (int)ceil($expert_details_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }//experts_list_by_company_id


    // post
    public function update_experts(Request $request)
    {

        $validator = validator($request->all(), PmRequest::update_experts());
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $old_expert=ExpertModel::where('id', $request->expert_id)->value('name');

        $expert_id = ExpertModel::where('id', $request->expert_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'remark' => $request->remark,
            'country_code' => $request->country_code,
        ]);

        SpecialisteExpertId::where('expert_id', $request->expert_id)->delete();

        //  \Log::info($request->all());
        foreach ($request->speciality_id as $key => $insert) {

            //$expert_id = ExpertModel::where('phone', $request->phone)->value('id');

            SpecialisteExpertId::create([

                'expert_id' => $request->expert_id,
                'speciality_id' => $request->speciality_id[$key],

            ]);
        }

        //module,action,affected_record_id,pm_id,pm_company_id
        \App\Services\PmLogService::pm_log_entry('expert','edit',$request->expert_id,$request->user()->id,$request->user()->pm_company_id,$old_expert, 'expert_edit');

        // return $this->sendResponse([], 'Expert updated successfully', 200, 200);
        return $this->sendResponse([],__(app()->getLocale().'.expert_updated'), 200, 200);

    }


    //POST
    public function delete_expert(Request $request)
    {
        $validator = validator($request->all(), ['expert_id' => 'required|numeric|exists:experts,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expert = \DB::table('maintenance_experts')
            ->where('expert_id', $request->expert_id)
            ->value('expert_id');

        if (blank($expert)) {

            $deleted_expert_name = \DB::table('experts')
                ->where('id', $request->expert_id)
                ->value('name');

               //module,action,affected_record_id,pm_id,pm_company_id,record_name
            \App\Services\PmLogService::pm_log_delete_entry('expert','delete',$request->expert_id,$request->user()->id,$request->user()->pm_company_id,$deleted_expert_name,'expert_deleted');

            //delete expert in expert table
            ExpertModel::where('id', $request->expert_id)->delete();
            //delete expert in specialiste_expert table.
            SpecialisteExpertId::where('expert_id', $request->expert_id)->delete();
            // delete expert in maintenance_expert table.
            MaintanceExpertModel::where('expert_id', $request->expert_id)->delete();

            // return $this->sendResponse([], 'Expert deleted successfully', 200, 200);
            return $this->sendResponse([],__(app()->getLocale().'.expert_deleted'), 200, 200);


        } else {

            // return $this->sendResponse([], 'Sorry expert can not be deleted at this moment due to maintenance request related to this expert found.', 201, 200);
            return $this->sendResponse([],__(app()->getLocale().'.expert_cannot_deleted_due_to_maintenance_request'), 201, 200);

        }
    }

    //post
    public function view_expert_by_expert_id(Request $request)
    {
        $validator = validator($request->all(), ['expert_id' => 'required|numeric|exists:experts,id']);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        $expert_details = ExpertModel::where('id', $request->expert_id)
            ->select('name', 'phone', 'remark', 'id' , 'country_code','email')
            ->first();

        $_temp_name = \DB::table('specialisties_expert_id')
            ->leftJoin('specialities', 'specialisties_expert_id.speciality_id', '=', 'specialities.id')
            ->where('specialisties_expert_id.expert_id', $request->expert_id)->select('specialities.name', 'specialities.id')->get();
        $expert_details->speciality = $_temp_name;

        $response = [
            'success' => true,
            'data'    => $expert_details,
            'message' => 'view_expert_by_expert_id',
            'status'  => 200
        ];
        return response()->json($response, 200);
    }


    //post
    public function search_expert(Request $request)
    {
        $validator = validator($request->all(), [
            'search_key' => 'required',
            'page' => 'required|numeric',
            'speciality_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
        }

        if ( ($request->speciality_id != 0) ) {
            $validator = validator($request->all(), [
                'speciality_id' => 'exists:specialities,id',
            ]);
            if ($validator->fails()) {
                return $this->sendSingleFieldError($validator->errors()->first(), 201, 200);
            }
        }

        $page = $request->page;
        $skip = $page ? 10 * ($page - 1) : 0;
        $search_key = $request->search_key;

        \DB::statement("SET SQL_MODE=''");
        $search_expert_query = ExpertModel::leftJoin('specialisties_expert_id', 'experts.id', '=', 'specialisties_expert_id.expert_id')
            ->leftJoin('specialities', 'specialisties_expert_id.speciality_id', '=', 'specialities.id')
            ->where('experts.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {

                $query->where('experts.name', 'LIKE', "%$search_key%")
                    ->orWhere('experts.phone', 'LIKE', "%$search_key%")
                    ->orWhere('experts.email', 'LIKE', "%$search_key%")
                    ->orWhere('specialities.name', 'LIKE', "%$search_key%");
            });

            if($request->speciality_id != 0){
                $search_expert_query->where('specialities.id', $request->speciality_id);
            }

            $search_expert = $search_expert_query->select(
                'experts.id',
                'experts.name',
                'experts.email',
                'experts.phone',
                'experts.country_code',
                'specialities.name as speciality_name',
            )
            ->take(10)
            ->skip($skip)
            ->groupBy('experts.id')
            ->get();


        $search_expert_count_query = ExpertModel::leftJoin('specialisties_expert_id', 'experts.id', '=', 'specialisties_expert_id.expert_id')
            ->leftJoin('specialities', 'specialisties_expert_id.speciality_id', '=', 'specialities.id')
            ->where('experts.pm_company_id', $request->user()->pm_company_id)
            ->where(function ($query) use ($search_key) {
                $query->where('experts.name', 'LIKE', "%$search_key%")
                    ->orWhere('experts.phone', 'LIKE', "%$search_key%")
                    ->orWhere('experts.email', 'LIKE', "%$search_key%")
                    ->orWhere('specialities.name', 'LIKE', "%$search_key%");
            });

        if($request->speciality_id != 0){
            $search_expert_count_query->where('specialities.id', $request->speciality_id);
        }

        $search_expert_count = $search_expert_count_query
        ->distinct('experts.id')
        ->count();

        $response = [
            'success' => true,
            'data'    => $search_expert,
            'message' => 'Search list',
            'pagecount'  => (int)ceil($search_expert_count / 10),
            'status'  => 200
        ];
        return response()->json($response, 200);
    }
}
