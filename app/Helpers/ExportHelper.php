<?php
namespace App\Helpers;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Illuminate\Support\Carbon;
use App\Models\MaintanceRequestModel;


class ExportHelper
{

    public static function generate_doc_for_buildings($writer , $pm_id){
        $writer->openToBrowser('buildings_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Building name'),
                WriterEntityFactory::createCell('Building code'),
                WriterEntityFactory::createCell('Address'),
                WriterEntityFactory::createCell('Location'),
                // WriterEntityFactory::createCell('Units'),
                WriterEntityFactory::createCell('Status')
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('buildings')->select('building_name', 'building_code', 'address', 'units' , 'status', 'location_link' )
            ->where('pm_company_id' , $pm_company_id)
            ->orderBy('id')
            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){
                       // \Log::info($_single_row->building_name);
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->building_code),
                                    WriterEntityFactory::createCell($_single_row->address),
                                    WriterEntityFactory::createCell($_single_row->location_link),
                                    // WriterEntityFactory::createCell($_single_row->units),
                                    WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Inactive')
                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }

    public static function generate_doc_for_available_units($writer , $pm_id){
        $writer->openToBrowser('available_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Building name'),
                WriterEntityFactory::createCell('Unit no'),
                WriterEntityFactory::createCell('Unit code'),
                WriterEntityFactory::createCell('Bathrooms'),
                WriterEntityFactory::createCell('Rooms'),
                // WriterEntityFactory::createCell('Address'),
                WriterEntityFactory::createCell('Monthly rent'),
                WriterEntityFactory::createCell('Area'),
                WriterEntityFactory::createCell('Description'),
                WriterEntityFactory::createCell('Status')
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('avaliable_units')->Join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
            ->select('avaliable_units.unit_code' , 'avaliable_units.bathrooms', 'avaliable_units.rooms',
            // 'avaliable_units.address' ,
            'avaliable_units.area_sqm', 'avaliable_units.description' ,
             'avaliable_units.monthly_rent', 'avaliable_units.unit_no' ,
             'buildings.building_name', 'avaliable_units.status')
            // ->where('buildings.property_manager_id' , $pm_id)
            ->where('buildings.pm_company_id', $pm_company_id )

            ->orderBy('avaliable_units.id')
            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){
                         //\Log::info(json_encode($_single_row));
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($_single_row->unit_code),
                                    WriterEntityFactory::createCell($_single_row->bathrooms),
                                    WriterEntityFactory::createCell($_single_row->rooms),
                                    // WriterEntityFactory::createCell($_single_row->address),
                                    WriterEntityFactory::createCell($_single_row->monthly_rent),
                                    WriterEntityFactory::createCell($_single_row->area_sqm),
                                    WriterEntityFactory::createCell($_single_row->description),
                                    WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Inactive')
                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }


    // public static function generate_doc_for_all_units($writer , $pm_id){
    //     $writer->openToBrowser('all_units_report.xlsx'); // stream data directly to the browser
    //         $cells = [
    //             WriterEntityFactory::createCell('Building name'),
    //             WriterEntityFactory::createCell('Unit no'),
    //             WriterEntityFactory::createCell('Unit code'),
    //             WriterEntityFactory::createCell('Bathrooms'),
    //             WriterEntityFactory::createCell('Rooms'),
    //             WriterEntityFactory::createCell('Address'),
    //             WriterEntityFactory::createCell('Monthly rent'),
    //             WriterEntityFactory::createCell('Area'),
    //             WriterEntityFactory::createCell('Description'),
    //             WriterEntityFactory::createCell('Status')
    //         ];
    //         /** add a row at a time */
    //         $singleRow = WriterEntityFactory::createRow($cells);
    //         $writer->addRow($singleRow);
    //         $multipleRows = array();
    //         \DB::table('avaliable_units')->Join('buildings', 'avaliable_units.building_id', '=', 'buildings.id')
    //         ->select('avaliable_units.unit_code' , 'avaliable_units.bathrooms', 'avaliable_units.rooms',
    //         'avaliable_units.address' , 'avaliable_units.area_sqm', 'avaliable_units.description' ,
    //          'avaliable_units.monthly_rent', 'avaliable_units.unit_no' ,
    //          'buildings.building_name', 'avaliable_units.status')
    //         ->where('buildings.property_manager_id' , $pm_id)
    //         ->orderBy('avaliable_units.id')
    //         ->chunk(30,function($_all_rows)use($multipleRows, $writer){
    //                 foreach($_all_rows as $_single_row){
    //                      //\Log::info(json_encode($_single_row));
    //                     $multipleRows[] =
    //                         WriterEntityFactory::createRow(
    //                             [
    //                                 WriterEntityFactory::createCell($_single_row->building_name),
    //                                 WriterEntityFactory::createCell($_single_row->unit_no),
    //                                 WriterEntityFactory::createCell($_single_row->unit_code),
    //                                 WriterEntityFactory::createCell($_single_row->bathrooms),
    //                                 WriterEntityFactory::createCell($_single_row->rooms),
    //                                 WriterEntityFactory::createCell($_single_row->address),
    //                                 WriterEntityFactory::createCell($_single_row->monthly_rent),
    //                                 WriterEntityFactory::createCell($_single_row->area_sqm),
    //                                 WriterEntityFactory::createCell($_single_row->description),
    //                                 WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Inactive')
    //                             ]
    //                             );
    //                 }// _single_row   foreach    end
    //             $writer->addRows($multipleRows);
    //             });// chunk end
    //             return $writer;
    // }


    public static function generate_doc_for_owner_list($writer , $pm_id){
        $writer->openToBrowser('owner_list_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Owner name'),
                WriterEntityFactory::createCell('Owner code'),
                WriterEntityFactory::createCell('Email'),
                WriterEntityFactory::createCell('Phone'),
                WriterEntityFactory::createCell('Remarks'),
                WriterEntityFactory::createCell('Status'),
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            \DB::table('owners')->select('name', 'owner_code' , 'email' , 'phone' , 'remarks' , 'status')
            ->where('pm_company_id', \DB::table('property_managers')->where('id', $pm_id )->value('pm_company_id'))
            ->orderBy('id')
            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){
                         //\Log::info(json_encode($_single_row));
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->name),
                                    WriterEntityFactory::createCell($_single_row->owner_code),
                                    WriterEntityFactory::createCell($_single_row->email),
                                    WriterEntityFactory::createCell($_single_row->phone),
                                    WriterEntityFactory::createCell($_single_row->remarks),
                                    WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Inactive')
                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }


    public static function generate_doc_for_tenant_unit_list($writer , $pm_id){
        // \Log::notice('generate_doc_for_tenant_unit_list pm_id '. $pm_id);
        $writer->openToBrowser('tenant_unit_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Unit no'),
                WriterEntityFactory::createCell('Unit code'),
                WriterEntityFactory::createCell('Building name'),
                WriterEntityFactory::createCell('Address'),
                WriterEntityFactory::createCell('Tenant Name'),
                WriterEntityFactory::createCell('Status'),
                WriterEntityFactory::createCell('Maintenance Included'),

            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('tenants_units')->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            // ->join('tenants', 'tenants_units.tenant_id', '=', 'tenants.id')
            ->select(
                'buildings.address' ,
                'buildings.building_name',

                'tenants_units.unit_no',
                'tenants_units.unit_code' ,
                'tenants_units.status',
                'tenants_units.tenant_id',
                'tenants_units.maintenance_included' ,
                // 'tenants.last_name' ,
            )
            ->where('buildings.pm_company_id', $pm_company_id )
            ->orderBy('tenants_units.id')
            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){

                        if($_single_row->tenant_id != 0){
                            $_temp_tenant = \DB::table('tenants')->where('id', $_single_row->tenant_id)
                                ->select('first_name','last_name')->first();

                            $tenant_name =  $_temp_tenant->first_name.' '.$_temp_tenant->last_name;
                            // \Log::notice('tenant_name '. $tenant_name);
                        }else{
                            $tenant_name = '';
                        }

                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($_single_row->unit_code),
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->address),
                                    WriterEntityFactory::createCell($tenant_name),
                                    // WriterEntityFactory::createCell($_single_row->first_name),
                                    WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Inactive'),
                                    WriterEntityFactory::createCell(($_single_row->maintenance_included ==1)?'Yes':'No')

                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }

    public static function generate_doc_for_contract_list($writer , $pm_id){

        $writer->openToBrowser('contract_tables_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Name'),
                WriterEntityFactory::createCell('Building name'),
                WriterEntityFactory::createCell('Unit No'),
                WriterEntityFactory::createCell('Tenant Name'),
                WriterEntityFactory::createCell('Start date'),
                WriterEntityFactory::createCell('Expiry date'),
                WriterEntityFactory::createCell('Status'),
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('contracts_tables')
            ->join('buildings', 'contracts_tables.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'contracts_tables.unit_id', '=', 'tenants_units.id')

            ->select(
                'buildings.building_name',
                'tenants_units.unit_no',

                'contracts_tables.name',
                'contracts_tables.start_date',
                'contracts_tables.end_date',
                'contracts_tables.status',
                'contracts_tables.Tenant_id',
                'contracts_tables.id',

                )
            ->where('contracts_tables.pm_company_id', $pm_company_id)
            ->orderBy('contracts_tables.id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){

                        if($_single_row->Tenant_id != 0){
                            $_temp_tenant = \DB::table('tenants')->where('id', $_single_row->Tenant_id)
                                ->select('first_name','last_name')->first();

                            $tenant_name =  $_temp_tenant->first_name.' '.$_temp_tenant->last_name;
                            // \Log::notice('tenant_name '. $tenant_name);
                        }else{
                            $tenant_name = '';
                        }

                        $is_expired = (Carbon::parse($_single_row->end_date) < Carbon::now())?1:0;

                        //change status in db if required.
                        if(($is_expired == 1) && ($_single_row->status == 1)){
                            \App\Models\ContractModel::where('id', $_single_row->id)->update(['status' => 0 ]);
                            $_single_row->status = 0;
                        }
                         //\Log::info(json_encode($_single_row));
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->name),
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($tenant_name),
                                    WriterEntityFactory::createCell(date('d M Y', strtotime($_single_row->start_date))),
                                    WriterEntityFactory::createCell(date('d M Y', strtotime($_single_row->end_date))),
                                    WriterEntityFactory::createCell(($_single_row->status ==1)?'Active':'Expired')
                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;

    }


    public static function generate_doc_for_payments($writer , $pm_id){
        $writer->openToBrowser('payments.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Currency'),
                WriterEntityFactory::createCell('Amount'),
                WriterEntityFactory::createCell('Cheque No.'),
                WriterEntityFactory::createCell('Tenant Name'),
                WriterEntityFactory::createCell('Building'),
                WriterEntityFactory::createCell('Unit No.'),
                WriterEntityFactory::createCell('Payment ID'),
                WriterEntityFactory::createCell('Payment Date'),
                WriterEntityFactory::createCell('Payment Status'),
                WriterEntityFactory::createCell('Payment Type'),
                WriterEntityFactory::createCell('Remark'),

            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');
            $currency_id = \DB::table('property_manager_companies')->where('id', $pm_company_id)->value('currency_id');
            $symbol = \DB::table('currencies')->where('id', $currency_id)->value('symbol');

            \DB::table('payments')
            ->join('buildings', 'payments.building_id', '=', 'buildings.id')
            ->join('tenants_units', 'payments.unit_id', '=', 'tenants_units.id')

            ->select(
                'buildings.building_name',
                'tenants_units.unit_no',

                'payments.amount',
                'payments.cheque_no',
                'payments.payment_code',
                'payments.remark',
                'payments.payment_date',
                'payments.tenant_id',
                'payments.status',
                'payments.payment_type',
                'payments.id',

                )
            ->where('payments.pm_company_id', $pm_company_id)
            ->orderBy('payments.id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer, $symbol ){
                    foreach($_all_rows as $_single_row){

                        if($_single_row->tenant_id != 0){
                            $_temp_tenant = \DB::table('tenants')->where('id', $_single_row->tenant_id)
                                ->select('first_name','last_name')->first();

                            $tenant_name =  $_temp_tenant->first_name.' '.$_temp_tenant->last_name;
                            // \Log::notice('tenant_name '. $tenant_name);
                        }else{
                            $tenant_name = '';
                        }


                        $pay_status_int = $_single_row->status;

                        $pay_status_string = \App\Services\PaymentStatusService::PaymentStatus($pay_status_int, 'payment_status');


                        // switch ($pay_status_int) {
                        //     case 1:
                        //         $pay_status_string = 'Upcoming Cheque';
                        //         break;
                        //     case 2:
                        //         $pay_status_string = 'Voided';
                        //         break;
                        //     case 3:
                        //         $pay_status_string = 'Settled';
                        //         break;
                        //     case 4:
                        //         $pay_status_string = 'Overdue';
                        //         break;
                        //     case 5:
                        //         $pay_status_string = 'Cheque Returned';
                        //         break;
                        //     case 6:
                        //         $pay_status_string = 'Voided';
                        //         break;
                        //     case 7:
                        //         $pay_status_string = 'Upcoming Payment';
                        //         break;
                        //     case 8:
                        //         $pay_status_string = 'Overdue';
                        //         break;
                        //     case 9:
                        //         $pay_status_string = 'Paid';
                        //         break;
                        //     case 10:
                        //         $pay_status_string = 'Payment In Default';
                        //         break;
                        //     }

                            $_single_row->status = $pay_status_string;


                         //\Log::info(json_encode($_single_row));
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($symbol),
                                    WriterEntityFactory::createCell($_single_row->amount),
                                    WriterEntityFactory::createCell($_single_row->cheque_no),
                                    WriterEntityFactory::createCell($tenant_name),
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($_single_row->payment_code),
                                    WriterEntityFactory::createCell(date('d M Y', strtotime($_single_row->payment_date))),
                                    WriterEntityFactory::createCell($pay_status_string),
                                    WriterEntityFactory::createCell(($_single_row->payment_type == 1)?'cheque':'manual'),
                                    WriterEntityFactory::createCell($_single_row->remark),
                                    ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }

    public static function generate_doc_for_all_tenant_list($writer , $pm_id){

        $writer->openToBrowser('tenants_tables_report.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('First Name'),
                WriterEntityFactory::createCell('Last Name'),
                WriterEntityFactory::createCell('Email'),
                WriterEntityFactory::createCell('Country Code'),
                WriterEntityFactory::createCell('Phone'),
                WriterEntityFactory::createCell('Building Name'),
                WriterEntityFactory::createCell('Unit No'),
                WriterEntityFactory::createCell('Status'),

            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('tenants')->leftjoin('tenants_units', 'tenants.id', '=', 'tenants_units.tenant_id')
            ->leftjoin('buildings', 'tenants_units.building_id', '=', 'buildings.id')
            ->where('tenants.pm_company_id', $pm_company_id)
            ->whereIn('tenants.status', [1,3])
            ->select(
                    'tenants.id',
                    'tenants.first_name',
                    'tenants.last_name',
                    'tenants.country_code',
                    'tenants.phone',
                    'tenants.email',
                    'buildings.building_name',
                    'tenants_units.unit_no',
                    'tenants.status',

                    
            )
            ->orderBy('tenants.id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){

                         //\Log::info(json_encode($_single_row));
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->first_name),
                                    WriterEntityFactory::createCell($_single_row->last_name),
                                    WriterEntityFactory::createCell($_single_row->email),
                                    WriterEntityFactory::createCell('+'.$_single_row->country_code),
                                    WriterEntityFactory::createCell($_single_row->phone), 
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell(($_single_row->status == 1)?'Approved':'Disconnected'),

                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;

            }


      public static function generate_doc_for_expert_list($writer , $pm_id){
        $writer->openToBrowser('expert.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Name'),
                WriterEntityFactory::createCell('Country Code'),
                WriterEntityFactory::createCell('Phone'),
                WriterEntityFactory::createCell('Email'),
                WriterEntityFactory::createCell('Remark'),
                WriterEntityFactory::createCell('Specialities'),

            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            \DB::table('experts')
            ->select(
                'name',
                'email',
                'phone',
                'id',
                'remark',
                'country_code',
                )
            ->where('pm_company_id', $pm_company_id)
            ->orderBy('id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                foreach($_all_rows as $_single_row){

                    $specialisties_experts = \DB::table('specialisties_expert_id')
                    ->where('expert_id', $_single_row->id)
                    ->select('speciality_id')
                    ->get();

                    $all_specialities = '';
                    
                    foreach($specialisties_experts as $_value){
                       $_name = \DB::table('specialities')
                       ->where('id', $_value->speciality_id)
                       ->value('name');

                    //    \Log::info(json_encode($_name));

                       if(!blank($_name)){
                        $all_specialities .= $_name;
                        $all_specialities .= ', '; 
                       }

                    }

                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->name),
                                    WriterEntityFactory::createCell($_single_row->country_code),
                                    WriterEntityFactory::createCell($_single_row->phone),
                                    WriterEntityFactory::createCell($_single_row->email),
                                    WriterEntityFactory::createCell($_single_row->remark),
                                    WriterEntityFactory::createCell($all_specialities),
                               ]);
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }


    public static function generate_doc_for_maintenance_request_list($writer , $pm_id){
        $writer->openToBrowser('maintenance_request.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Requested By'),
                WriterEntityFactory::createCell('Requested ID'),
                WriterEntityFactory::createCell('Building'),
                WriterEntityFactory::createCell('Unit No.'),
                WriterEntityFactory::createCell('Request For'),
                WriterEntityFactory::createCell('Status'),
                WriterEntityFactory::createCell('Request Date'),
                WriterEntityFactory::createCell('Assigned Experts'),
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            $all_data_query = MaintanceRequestModel::join('tenants_units', 'maintance_requests.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

            ->select(
                'maintance_requests.id',
                'maintance_requests.maintenance_request_id',
                'maintance_requests.unit_id',
                'maintance_requests.building_id',
                'maintance_requests.tenant_id',
                'maintance_requests.status',
                'maintance_requests.request_code',
                'maintance_requests.created_at',

                'buildings.building_name',
                'tenants_units.unit_no',
                )
            ->where('maintance_requests.pm_company_id', $pm_company_id)
            ->orderBy('maintance_requests.id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){


                            $_single_row->request_date = date('d M Y', strtotime($_single_row->created_at));
            
                            switch ($_single_row->status) {
                                case 1:
                                    $status_string = 'Request Raised';
                                    break;
                                case 2:
                                    $status_string = 'Request Assigned';
                                    break;
                                case 3:
                                    $status_string = 'Request completed';
                                    break;
                                case 4:
                                    $status_string = 'Request is on hold';
                                    break;
                                case 5:
                                    $status_string = 'Request canceled';
                                    break;
                            }
                            $_single_row->status = $status_string;
            
                            if($_single_row->tenant_id != 0){
                                //get tenant details
                                $tenant_details = \DB::table('tenants')->where('id', $_single_row->tenant_id)->select('last_name','first_name')->first();
            
                                $_single_row->requested_by = !blank($tenant_details) ? $tenant_details->first_name.' '.$tenant_details->last_name : '';
                            }else{
                                $_single_row->requested_by = '';
                            }
            
                            //get maitinance_request_name
                            $_single_row->request_for = \DB::table('maitinance_request_for')->where('id', $_single_row->maintenance_request_id)->value('maitinance_request_name');
            
                          
                            $maintenance_experts = \DB::table('maintenance_experts')
                            ->where('maintenance_id', $_single_row->id)
                            ->select('expert_id')
                            ->get();
        
                            $all_maintenance_experts = '';
                            
                            foreach($maintenance_experts as $_value){
                               $_name = \DB::table('experts')
                               ->where('id', $_value->expert_id)
                               ->select('name','phone')
                               ->first();
        
                               if(!blank($_name)){
                                $all_maintenance_experts .= $_name->name.' '.$_name->phone;
                                $all_maintenance_experts .= ', '; 
                               }
                            }
        
                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->requested_by),
                                    WriterEntityFactory::createCell($_single_row->request_code),
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($_single_row->request_for),
                                    WriterEntityFactory::createCell($_single_row->status),
                                    WriterEntityFactory::createCell($_single_row->request_date),
                                    WriterEntityFactory::createCell($all_maintenance_experts),
                                    ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }


    public static function generate_doc_for_maintenance_expenses_list($writer , $pm_id){
        $writer->openToBrowser('maintenance_expenses.xlsx'); // stream data directly to the browser
            $cells = [
                WriterEntityFactory::createCell('Building'),
                WriterEntityFactory::createCell('Unit No.'),
                WriterEntityFactory::createCell('Requested ID'),
                WriterEntityFactory::createCell('Expenses'),
                WriterEntityFactory::createCell('Amount'),
                WriterEntityFactory::createCell('Date'),
            ];
            /** add a row at a time */
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
            $multipleRows = array();

            $pm_company_id = \DB::table('property_managers')->where('id', $pm_id)->value('pm_company_id');

            $all_data_query = \DB::table('expenses_items')
            ->leftJoin('expenses', 'expenses_items.expenses_id', '=', 'expenses.id')
            ->join('tenants_units', 'expenses.unit_id', '=', 'tenants_units.id')
            ->join('buildings', 'tenants_units.building_id', '=', 'buildings.id')

            ->select(
                'expenses.id',
                'expenses.request_id',
                'expenses.unit_id',
                'expenses.building_id',
                'buildings.building_name',
                'tenants_units.unit_no',
                'expenses_items.currency_id',
                'expenses_items.cost',
                'expenses_items.date',
                'expenses_items.expenses_lines_id',
                )
            ->where('expenses.pm_company_id', $pm_company_id)
            ->orderBy('expenses.id')

            ->chunk(30,function($_all_rows)use($multipleRows, $writer){
                    foreach($_all_rows as $_single_row){
                      

                            $request_details = \DB::table('maintance_requests')->where('id', $_single_row->request_id)->select('request_code')->first();
        
                            $_single_row->request_code = !blank($request_details) ? $request_details->request_code : '';
        
                            $currency = \DB::table('currencies')
                                ->where('id', $_single_row->currency_id)
                                ->select('symbol')
                                ->first();
        
                            $symbol = !blank($currency) ? $currency->symbol : '';
        
                            $_single_row->expenses = \DB::table('expenseslines')
                            ->where('id', $_single_row->expenses_lines_id)
                            ->value('expenseslines_name');
        
                            $_single_row->amount = $symbol.$_single_row->cost ;
                            $_single_row->date = date('d M Y', strtotime($_single_row->date));
        

                        $multipleRows[] =
                            WriterEntityFactory::createRow(
                                [
                                    WriterEntityFactory::createCell($_single_row->building_name),
                                    WriterEntityFactory::createCell($_single_row->unit_no),
                                    WriterEntityFactory::createCell($_single_row->request_code),
                                    WriterEntityFactory::createCell($_single_row->expenses),
                                    WriterEntityFactory::createCell($_single_row->amount),
                                    WriterEntityFactory::createCell($_single_row->date),
                                ]
                                );
                    }// _single_row   foreach    end
                $writer->addRows($multipleRows);
                });// chunk end
                return $writer;
    }

}
