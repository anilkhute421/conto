<?php
namespace App\Helpers;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Illuminate\Support\Carbon;
use DB;

class DrillDownExportHelper
{


    public static function excel_export_contract_expiring2m($writer, $pm_company_id, $_loop_count){
        $writer->openToBrowser('Contract_Expiring_In_2_months.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Contract Expiring In 2 months'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        $cells = [
            WriterEntityFactory::createCell('Unit No.'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Start Date'),
            WriterEntityFactory::createCell('End Date'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $_all_rows = DB::select("CALL DrillDownTotalContractsExpiring2M(".$pm_company_id.",".$limit.",".$offset.")");

            foreach($_all_rows as $_single_row){

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->UnitNo),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->TenantName),
                        WriterEntityFactory::createCell($_single_row->start_date),
                        WriterEntityFactory::createCell($_single_row->end_date),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }

    // ---------------------------------------closed request ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_closed_request($writer, $pm_company_id, $request){
        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        //1 make header
        $writer->openToBrowser('Total_Number_of_Maintenance_Closed_requests.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Number of Maintenance Closed requests'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }

        $cells = [
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Unit Id'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Created At'),
            WriterEntityFactory::createCell('Request For'),
            WriterEntityFactory::createCell('Status'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalClosedRequests12m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.",";

            }elseif($request->month_id == 9){
                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalClosedRequests9m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.",";

            }elseif($request->month_id == 6){
                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalClosedRequests6m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.",";

            }elseif($request->month_id == 1){
                $_maintenace_closed_record = DB::select("CALL TotalClosedRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalClosedRequests1m(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.",";
            }
        }
        else{
            $_maintenace_closed_record = DB::select("CALL TotalClosedRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_procedure_name = "CALL DrillDownTotalClosedRequestsDRange(".$pm_company_id.",".$request->building_id.",".$request->owner_id.",".$request->tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_total = $_maintenace_closed_record[0]->TotalPaymentsAmount;
        $_loop_count = (int)ceil($_total / 10);

        return self::write_excel_closed_request($writer, $_loop_count, $_procedure_name);
    }//closed_request


    //write rows to excel
    public static function write_excel_closed_request($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $_all_rows = DB::select($_procedure_name.$limit.",".$offset.")");

            foreach($_all_rows as $_single_row){

                    switch ($_single_row->status) {
                        case 1:
                            $status_string = 'Raised';
                            break;
                        case 2:
                            $status_string = 'Assigned';
                            break;
                        case 3:
                            $status_string = 'Completed';
                            break;
                        case 4:
                            $status_string = 'On hold';
                            break;
                        case 5:
                            $status_string = 'Canceled';
                            break;
                    }

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->unit_id),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->created_at),
                        WriterEntityFactory::createCell($_single_row->RequestFor),
                        WriterEntityFactory::createCell($status_string),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }// write closed_request

    // ---------------------------------------open request ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_open_request($writer, $pm_company_id, $request){

        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        $building_id = $request->building_id;
        $owner_id = $request->owner_id;
        $tenant_id = $request->tenant_id;

        //1 make header
        $writer->openToBrowser('Total_Number_of_Maintenance_Open_requests.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Number of Maintenance Open requests'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }


        $cells = [
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Unit Id'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Created At'),
            WriterEntityFactory::createCell('Request For'),
            WriterEntityFactory::createCell('Status'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_maintenace_open_record = DB::select("CALL TotalOpenRequests12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalOpenRequests12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 9){
                $_maintenace_open_record = DB::select("CALL TotalOpenRequests9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalOpenRequests9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 6){
                $_maintenace_open_record = DB::select("CALL TotalOpenRequests6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalOpenRequests6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 1){
                $_maintenace_open_record = DB::select("CALL TotalOpenRequests1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalOpenRequests1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";
            }
        }
        else{
            $_maintenace_open_record = DB::select("CALL TotalOpenRequestsDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_procedure_name = "CALL DrillDownTotalOpenRequestsDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_total = $_maintenace_open_record[0]->TotalPaymentsAmount;
        $_loop_count = (int)ceil($_total / 10);

        // \Log::alert('write_excel_open_request loop count '.$_loop_count);
        return self::write_excel_open_request($writer, $_loop_count, $_procedure_name);
    }


    //write rows to excel
    public static function write_excel_open_request($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $final_query = $_procedure_name.$limit.",".$offset.")";
            // \Log::alert('write_excel_open_request query '.$final_query);

            $_all_rows = DB::select($final_query);

            foreach($_all_rows as $_single_row){

                    switch ($_single_row->status) {
                        case 1:
                            $status_string = 'Raised';
                            break;
                        case 2:
                            $status_string = 'Assigned';
                            break;
                        case 3:
                            $status_string = 'Completed';
                            break;
                        case 4:
                            $status_string = 'On hold';
                            break;
                        case 5:
                            $status_string = 'Canceled';
                            break;
                    }

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->unit_id),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->created_at),
                        WriterEntityFactory::createCell($_single_row->RequestFor),
                        WriterEntityFactory::createCell($status_string),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }


// ---------------------------------------set_payment ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_set_payment($writer, $pm_company_id, $request){
        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        $building_id = $request->building_id;
        $owner_id = $request->owner_id;
        $tenant_id = $request->tenant_id;

        //1 make header
        $writer->openToBrowser('Total_Amount_of_Payments_Settled.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Amount of Payments Settled'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }


        $cells = [
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Unit No.'),
            WriterEntityFactory::createCell('Payment Type'),
            WriterEntityFactory::createCell('Payment Date'),
            WriterEntityFactory::createCell('Amount'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_total_records = DB::select("CALL TotalSetPaymentsCount12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalSetPayments12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 9){
                $_total_records = DB::select("CALL TotalSetPaymentsCount9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalSetPayments9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 6){
                $_total_records = DB::select("CALL TotalSetPaymentsCount6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalSetPayments6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 1){
                $_total_records = DB::select("CALL TotalSetPaymentsCount1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalSetPayments1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";
            }
        }
        else{
            $_total_records = DB::select("CALL TotalSetPaymentsCountDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_procedure_name = "CALL DrillDownTotalSetPaymentsDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_total = $_total_records[0]->TotalPayments;
        $_loop_count = (int)ceil($_total / 10);

        // \Log::alert('write_excel_open_request loop count '.$_loop_count);
        return self::write_excel_set_pay($writer, $_loop_count, $_procedure_name);
    }


    //write rows to excel
    public static function write_excel_set_pay($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $final_query = $_procedure_name.$limit.",".$offset.")";
            // \Log::alert('write_excel_open_request query '.$final_query);

            $_all_rows = DB::select($final_query);

            foreach($_all_rows as $_single_row){

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->UnitNumber),
                        WriterEntityFactory::createCell($_single_row->payment_type == 0 ? 'Manual' : 'Cheque'), /* manual ->0 , cheque ->1*/
                        WriterEntityFactory::createCell($_single_row->payment_date),
                        WriterEntityFactory::createCell($_single_row->amount),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }



// --------------------------------------- overdue_payment ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_overdue_payment($writer, $pm_company_id, $request){
        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        $building_id = $request->building_id;
        $owner_id = $request->owner_id;
        $tenant_id = $request->tenant_id;

        $writer->openToBrowser('Total_Amount_of_Payments_Overdue.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Amount of Payments Overdue'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }

        $cells = [
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Unit No.'),
            WriterEntityFactory::createCell('Payment Type'),
            WriterEntityFactory::createCell('Payment Date'),
            WriterEntityFactory::createCell('Amount'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_total_records = DB::select("CALL TotalOverDuePaymentsCount12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_total = $_total_records[0]->TotalPaymentsAmount;
                $_procedure_name = "CALL DrillDownTotalOverDuePayments12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 9){
                $_total_records = DB::select("CALL TotalOverDuePaymentsCount9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_total = $_total_records[0]->TotalPayments;
                $_procedure_name = "CALL DrillDownTotalOverDuePayments9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 6){
                $_total_records = DB::select("CALL TotalOverDuePaymentsCount6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_total = $_total_records[0]->TotalPayments;
                $_procedure_name = "CALL DrillDownTotalOverDuePayments6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 1){
                $_total_records = DB::select("CALL TotalOverDuePaymentsCount1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_total = $_total_records[0]->TotalPayments;
                $_procedure_name = "CALL DrillDownTotalOverDuePayments1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";
            }
        }
        else{
            $_total_records = DB::select("CALL TotalOverDuePaymentsCountDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_total = $_total_records[0]->TotalPayments;
            $_procedure_name = "CALL DrillDownTotalOverDuePaymentsDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_loop_count = (int)ceil($_total / 10);

        return self::write_excel_overdue_pay($writer, $_loop_count, $_procedure_name);
    }


    //write rows to excel
    public static function write_excel_overdue_pay($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $final_query = $_procedure_name.$limit.",".$offset.")";

            $_all_rows = DB::select($final_query);

            foreach($_all_rows as $_single_row){

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->UnitNumber),
                        WriterEntityFactory::createCell($_single_row->payment_type == 0 ? 'Manual' : 'Cheque'), /* manual ->0 , cheque ->1*/
                        WriterEntityFactory::createCell($_single_row->payment_date),
                        WriterEntityFactory::createCell($_single_row->amount),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }



// --------------------------------------- upcoming_payment ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_upcoming_payment($writer, $pm_company_id, $request){
        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        $building_id = $request->building_id;
        $owner_id = $request->owner_id;
        $tenant_id = $request->tenant_id;

        $writer->openToBrowser('Total_Amount_of_Payments_Upcoming.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Amount of Payments Upcoming'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }

        $cells = [
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Unit No.'),
            WriterEntityFactory::createCell('Payment Type'),
            WriterEntityFactory::createCell('Payment Date'),
            WriterEntityFactory::createCell('Amount'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_total_records = DB::select("CALL TotalUpcomingPaymentsCount12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalUpcomingPayments12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 9){
                $_total_records = DB::select("CALL TotalUpcomingPaymentsCount9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalUpcomingPayments9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 6){
                $_total_records = DB::select("CALL TotalUpcomingPaymentsCount6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalUpcomingPayments6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 1){
                $_total_records = DB::select("CALL TotalUpcomingPaymentsCount1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalUpcomingPayments1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";
            }
        }
        else{
            $_total_records = DB::select("CALL TotalUpcomingPaymentsCountDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_procedure_name = "CALL DrillDownTotalUpcomingPaymentsDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_total = $_total_records[0]->TotalPayments;
        $_loop_count = (int)ceil($_total / 10);

        return self::write_excel_upcoming_pay($writer, $_loop_count, $_procedure_name);
    }


    //write rows to excel
    public static function write_excel_upcoming_pay($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $final_query = $_procedure_name.$limit.",".$offset.")";

            $_all_rows = DB::select($final_query);

            foreach($_all_rows as $_single_row){

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->UnitNumber),
                        WriterEntityFactory::createCell($_single_row->payment_type == 0 ? 'Manual' : 'Cheque'), /* manual ->0 , cheque ->1*/
                        WriterEntityFactory::createCell($_single_row->payment_date),
                        WriterEntityFactory::createCell($_single_row->amount),
                    ]
                );

            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }



// --------------------------------------- expenses ----------------------------------------------------

    //write header, loop count, if else
    public static function excel_export_card_maintenance_expense($writer, $pm_company_id, $request){
        $tenant_name = '';
        $owner_name = '';
        $building_name = '';

        if ($request->tenant_id != 'NULL') {
            $tenant = \DB::table('tenants')->where('id', $request->tenant_id)->select('first_name','last_name')->first();
            $tenant_name = $tenant->first_name.' '.$tenant->last_name;
        }else{
            $tenant_name = 'All';
        }
        if ($request->owner_id != 'NULL') {
            $owner_name = \DB::table('owners')->where('id', $request->owner_id)->value('name');
        }else{
            $owner_name = 'All';
        }
        if ($request->building_id != 'NULL') {
            $building_name = \DB::table('buildings')->where('id', $request->building_id)->value('building_name');
        }else{
            $building_name = 'All';
        }

        $_procedure_name = '';
        $building_id = $request->building_id;
        $owner_id = $request->owner_id;
        $tenant_id = $request->tenant_id;

        $writer->openToBrowser('Total_Amount_of_Maintenance_Expenses.xlsx'); // stream data directly to the browser

        $cells = [
            WriterEntityFactory::createCell('Total Amount of Maintenance Expenses'),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);

        if ($request->time_key == 'month') {
            $cells = [
                WriterEntityFactory::createCell('Months'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->month_id),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

        }else{
            $cells = [
                WriterEntityFactory::createCell('Date'),
                WriterEntityFactory::createCell('Buildings'),
                WriterEntityFactory::createCell('Tenants'),
                WriterEntityFactory::createCell('Owner'),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);

            $cells = [
                WriterEntityFactory::createCell($request->date_from.' to '.$request->date_to),
                WriterEntityFactory::createCell($building_name),
                WriterEntityFactory::createCell($tenant_name),
                WriterEntityFactory::createCell($owner_name),
            ];
            $singleRow = WriterEntityFactory::createRow($cells);
            $writer->addRow($singleRow);
        }

        $cells = [
            WriterEntityFactory::createCell('Unit No.'),
            WriterEntityFactory::createCell('Building Name'),
            WriterEntityFactory::createCell('Tenant Name'),
            WriterEntityFactory::createCell('Currency'),
            WriterEntityFactory::createCell('Cost'),
            WriterEntityFactory::createCell('Expense'),
        ];
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);


        //2 all if else
        if ($request->time_key == 'month') {

            if($request->month_id == 12){
                $_total_records = DB::select("CALL TotalExpensesCount12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalExpensesAmount12m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 9){
                $_total_records = DB::select("CALL TotalExpensesCount9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalExpensesAmount9m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 6){
                $_total_records = DB::select("CALL TotalExpensesCount6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalExpensesAmount6m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";

            }elseif($request->month_id == 1){
                $_total_records = DB::select("CALL TotalExpensesCount1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.")");
                $_procedure_name = "CALL DrillDownTotalExpensesAmount1m(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.",";
            }
        }
        else{
            $_total_records = DB::select("CALL TotalExpensesCountDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".")");
            $_procedure_name = "CALL DrillDownTotalExpensesAmountDRange(".$pm_company_id.",".$building_id.",".$owner_id.",".$tenant_id.","."'".$request->date_from."'".","."'".$request->date_to."'".",";
        }

        $_total = $_total_records[0]->TotalExpenses;
        $_loop_count = (int)ceil($_total / 10);

        return self::write_excel_expenses($writer, $_loop_count, $_procedure_name);
    }


    //write rows to excel
    public static function write_excel_expenses($writer, $_loop_count, $_procedure_name){

        for($i=1;$i<=$_loop_count;$i++){
            $multipleRows = array();

            $page = $i;
            $offset = $page ? 10 * ($page - 1) : 0;
            $limit = 10;

            $final_query = $_procedure_name.$limit.",".$offset.")";

            $_all_rows = DB::select($final_query);

            foreach($_all_rows as $_single_row){

                $multipleRows[] =
                WriterEntityFactory::createRow(
                    [
                        WriterEntityFactory::createCell($_single_row->UnitNumber),
                        WriterEntityFactory::createCell($_single_row->BuildingName),
                        WriterEntityFactory::createCell($_single_row->TenantNAme),
                        WriterEntityFactory::createCell($_single_row->Currency),
                        WriterEntityFactory::createCell($_single_row->cost),
                        WriterEntityFactory::createCell($_single_row->ExpensDes),
                    ]
                );
            }// _single_row   foreach    end
            $writer->addRows($multipleRows);
        }// for end
        return $writer;
    }



}
