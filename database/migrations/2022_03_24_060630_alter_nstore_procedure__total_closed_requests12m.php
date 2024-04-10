<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNStoreProcedureTotalClosedRequests12m extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        $TotalClosedRequests12m = "CREATE PROCEDURE `TotalClosedRequests12m` (IN tenant_id int, IN owner_id int, IN building_id int, IN pm_company_id int)
        
        BEGIN

         IF pm_company_id != 0 AND owner_id != 0 AND building_id = 0 AND tenant_id = 0 THEN
      SELECT COUNT(*)   
      FROM maintance_requests   
      JOIN tenants_units  
      ON maintance_requests.unit_id = tenants_units.id
      JOIN owners  
      ON tenants_units.owner_id = owners.id
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      tenants_units.owner_id = owner_id 
      and  
     (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and
      maintance_requests.status = 3;

  ELSEIF pm_company_id != 0 AND owner_id = 0 AND building_id != 0 AND tenant_id = 0 THEN
      SELECT COUNT(*)   
      FROM maintance_requests    
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      maintance_requests.building_id = building_id 
      and 
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3;
      
 ELSEIF pm_company_id != 0 AND tenant_id != 0 AND owner_id = 0 AND building_id = 0 THEN
      SELECT COUNT(*)   
      FROM maintance_requests
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      maintance_requests.tenant_id = tenant_id 
      and 
     (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3;

ELSEIF pm_company_id != 0 AND owner_id != 0 AND building_id != 0 AND tenant_id = 0 THEN
  SELECT COUNT(*)   
      FROM maintance_requests   
      JOIN tenants_units  
      ON maintance_requests.unit_id = tenants_units.id
      JOIN owners  
      ON tenants_units.owner_id = owners.id
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      tenants_units.owner_id = owner_id
      and  
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3
      and 
      maintance_requests.building_id = building_id;
      
      ELSEIF pm_company_id != 0 AND owner_id != 0 AND tenant_id != 0 AND building_id = 0 THEN
  SELECT COUNT(*)   
      FROM maintance_requests   
      JOIN tenants_units  
      ON maintance_requests.unit_id = tenants_units.id
      JOIN owners  
      ON tenants_units.owner_id = owners.id
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      tenants_units.owner_id = owner_id 
      and  
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3
      and 
      maintance_requests.tenant_id = tenant_id;
      
      ELSEIF pm_company_id != 0 AND building_id != 0 AND tenant_id != 0 AND owner_id = 0 THEN
  SELECT COUNT(*)   
      FROM maintance_requests   
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      maintance_requests.building_id = building_id 
      and  
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3
      and 
      maintance_requests.tenant_id = tenant_id;
      
      ELSEIF pm_company_id != 0 AND owner_id != 0 AND building_id != 0 AND tenant_id != 0 THEN
  SELECT COUNT(*)   
      FROM maintance_requests 
      JOIN tenants_units  
      ON maintance_requests.unit_id = tenants_units.id
      JOIN owners  
      ON tenants_units.owner_id = owners.id  
      WHERE maintance_requests.pm_company_id = pm_company_id
      and 
      maintance_requests.building_id = building_id 
      and  
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3
      and 
      maintance_requests.tenant_id = tenant_id
      and
      tenants_units.owner_id = owner_id;
  
  
  ELSEIF pm_company_id != 0 AND building_id = 0 AND tenant_id = 0 AND owner_id = 0 THEN
  SELECT COUNT(*)   
      FROM maintance_requests   
      WHERE maintance_requests.pm_company_id = pm_company_id
  
      and  
      (maintance_requests.created_at BETWEEN SUBDATE(curdate(), INTERVAL 12 MONTH) AND curdate()) 
      and 
      maintance_requests.status = 3;
     
   
 END IF;

        END;
        ";

\DB::unprepared($TotalClosedRequests12m);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
