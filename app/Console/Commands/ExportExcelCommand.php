<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use App\Helpers\ExportHelper;
use DB;

class ExportExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:export  {pm_id } {export_type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         ini_set('max_execution_time', 0);
         $pm_id = $this->argument('pm_id');
         $export_type = $this->argument('export_type');
        try{
           $writer = WriterEntityFactory::createXLSXWriter();
            if($export_type == 'building'){
                $writer =  ExportHelper::generate_doc_for_buildings($writer,$pm_id);
            }
            if($export_type == 'available_units'){
                $writer =  ExportHelper::generate_doc_for_available_units($writer,$pm_id);
            }
            // if($export_type == 'all_units'){
            //     $writer =  ExportHelper::generate_doc_for_all_units($writer,$pm_id);
            // }
            if($export_type == 'owner_list'){
                $writer =  ExportHelper::generate_doc_for_owner_list($writer,$pm_id);
            }
            if($export_type == 'tenant_units'){
                $writer =  ExportHelper::generate_doc_for_tenant_unit_list($writer,$pm_id);
            }
            if($export_type == 'contracts_tables'){
                $writer =  ExportHelper::generate_doc_for_contract_list($writer,$pm_id);
            }
            if($export_type == 'payments'){
                $writer =  ExportHelper::generate_doc_for_payments($writer,$pm_id);
            }
            if($export_type == 'all_tenant'){
                $writer =  ExportHelper::generate_doc_for_all_tenant_list($writer,$pm_id);
            }
            if($export_type == 'expert'){
                $writer =  ExportHelper::generate_doc_for_expert_list($writer,$pm_id);
            }
            if($export_type == 'maintenance_request'){
                $writer =  ExportHelper::generate_doc_for_maintenance_request_list($writer,$pm_id);
            }
            if($export_type == 'maintenance_expenses'){
                $writer =  ExportHelper::generate_doc_for_maintenance_expenses_list($writer,$pm_id);
            }
            $writer->close();
        }catch(\Exception $e){
            \Log::error('-------------Exception excel export ------------'.json_encode($e));
        }
    }
}
