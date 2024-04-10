<?php

namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiBaseController;
use Illuminate\Support\Carbon;


class CronController extends ApiBaseController
{

    // GET
    public function expireContracts(Request $request){
        ini_set('max_execution_time', 0);

        \Log::notice('cron expireContracts running');

        \DB::table('contracts_tables')
            ->select(
                'end_date',
                'id',
                'status'
                )
            ->where('status', 1)
            ->whereDate('end_date', '<', Carbon::now())
            ->orderBy('id') // required with chunk
            ->chunk(30,function($_all_rows){
                foreach($_all_rows as $_single_row){

                //status 0 expired, 1 active

                // $is_expired = (Carbon::parse($_single_row->end_date) < Carbon::now())?1:0;

                //change status in db if required.
                // if(($is_expired == 1) && ($_single_row->status == 1)){
                    \App\Models\ContractModel::where('id', $_single_row->id)->update(['status' => 0 ]);
                // }

            }// _single_row   foreach    end
        });// chunk end

    }




    // GET
    /*
    cheques/manual           upcoming payment(1)
    cheques/manual           voided(2)
    cheques/manual           settled (3)
    cheques/manual           Overdue payment(4)
    cheques           cheque returned(5)
    cheques/manual              Payment In Defaul(6)
    */
    public function expirePaymentStatusChange(Request $request){
        ini_set('max_execution_time', 0);

        \Log::notice('cron expirePaymentStatusChange running');

        \DB::table('payments')
        ->select(
            'payment_date',
            'id',
            'status'
            )
        ->where('status', 1)
        ->whereDate('payment_date', '<', Carbon::now())
        ->orderBy('id') // required with chunk
        ->chunk(30,function($_all_rows){
            foreach($_all_rows as $_single_row){

            //status 0 expired, 1 active

            // $is_expired = (Carbon::parse($_single_row->payment_date) < Carbon::now())?1:0;

            //change status in db if required.
            // if(($is_expired == 1) && ($_single_row->status == 1)){
                \DB::table('payments')->where('id', $_single_row->id)->update(['status' => 4 ]);
            // }

            }// _single_row   foreach    end
        });// chunk end


    }
}
