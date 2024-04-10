<?php

namespace App\Services;

use Storage;
use Log;
use DB;

class PaymentStatusService
{

    //\Storage::disk('azure')->put($imageName, \File::get($file));

    //function for delete file
    //module,action,affected_record_id,pm_id,pm_company_id,record_name
    public static function PaymentStatus($pay_status_int, $log_message)
    {

        try {

            switch ($pay_status_int) {
                case 1:
                    $pay_status_string = 'Upcoming Payment';
                    break;
                case 2:
                    $pay_status_string = 'Voided';
                    break;
                case 3:
                    $pay_status_string = 'Settled';
                    break;
                case 4:
                    $pay_status_string = 'Overdue Payment';
                    break;
                case 5:
                    $pay_status_string = 'Cheque Returned';
                    break;
                case 6:
                    $pay_status_string = 'Payment In Default';
                    break;
                // case 7:
                //     $pay_status_string = 'Upcoming Payment';
                //     break;
                // case 8:
                //     $pay_status_string = 'Overdue';
                //     break;
                // case 9:
                //     $pay_status_string = 'Paid';
                //     break;
                // case 10:
                //     $pay_status_string = 'Payment In Default';
                //     break;
            }

            return  $pay_status_string;

        } catch (\Exception $e) {

            Log::error('---------pm_log_entry------ ' . $log_message . ' ------------- ' . $e);
        }
    }




    // starting logs.
    // ->buildings - done.(remaining delete)
    // ->available unit - done.(remaining delete)
    // ->unit - done(remaining delete)
    // ->Owners - done(remaining delete,status(c))
    // ->tenant - done(remaining delete,status(c))
    // ->contract - done(remaining delete,status(c))
    // ->payment - done(remaining delete,status(c))
    // ->Maintenance - done(remaining delete,status(c))
    // ->Expenses - done(remaining delete,status(c))
    // ->Expert - done(remaining delete,status(c))
}
