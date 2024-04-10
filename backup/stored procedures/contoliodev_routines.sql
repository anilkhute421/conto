-- MySQL dump 10.13  Distrib 8.0.23, for Win64 (x86_64)
--
-- Host: beastmaindevdb.mysql.database.azure.com    Database: contoliodev
-- ------------------------------------------------------
-- Server version	5.6.47.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'contoliodev'
--
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalActiveBuildings` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalActiveBuildings`(
	IN PMCompanyID INT,
          in Limitn int,
    in offsetn int
)
BEGIN
	SELECT id,b.building_name,(select count(u.id) from contoliodev.tenants_units u where  u.building_id = b.id) TotalUnits
,b.location_link
FROM contoliodev.buildings b where status = 1
and b.pm_company_id = PMCompanyID
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalActiveUnits` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalActiveUnits`(
	in PMCompanyID int,
        in Limitn int,
    in offsetn int
)
BEGIN
SELECT id,u.unit_no, (select b.building_name from buildings b where u.building_id = b.id) BuildingName
, ifnull(if(u.tenant_id = 0, 'No Tenant',(select concat(t.first_name,' ',t.last_name) from tenants t where u.tenant_id = t.id)),'No Tenant') TenantName
,u.rooms,u.bathrooms,u.monthly_rent, ifnull((select ow.name from owners ow where ow.id = u.owner_id),'Owner Not Specified') OwnerName
FROM tenants_units u where status = 1
and pm_company_id = PMCompanyID
 limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalClosedRequests12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalClosedRequests12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in Limitn int,
    in offsetn int
 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -12 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalClosedRequests1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalClosedRequests1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -1 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalClosedRequests6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalClosedRequests6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -6 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalClosedRequests9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalClosedRequests9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
       in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -9 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalClosedRequestsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalClosedRequestsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in StartDate Date,
    in EndDate Date,
         in Limitn int,
    in offsetn int
 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
and m.created_at >= startDate and m.created_at <= EndDate
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3)
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalContractsExpiring2M` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalContractsExpiring2M`(
	in PMCompanyID int,
        in Limitn int,
    in offsetn int
)
BEGIN
SELECT id,(select un.unit_no from tenants_units un where un.id = u.unit_id) as 'UnitNo', (select b.building_name from buildings b where u.building_id = b.id) BuildingName
, ifnull(if(u.tenant_id = 0, 'No Tenant',(select concat(t.first_name,' ',t.last_name) from tenants t where u.tenant_id = t.id)),'No Tenant') TenantName
,u.start_date,u.end_date
FROM contoliodev.contracts_tables u where 
pm_company_id = PMCompanyID
and end_date <= date_add(SYSDATE(), interval 2 month) and end_date >= SYSDATE()
 limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmount12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -12 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmount12mTestPaging` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmount12mTestPaging`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -12 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -1 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
         in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -6 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -9 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalExpensesAmountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalExpensesAmountDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
       in StartDate Date,
    in EndDate Date,
          in Limitn int,
    in offsetn int
 
)
BEGIN
select 
id,(Select unit_no from tenants_units tu where tu.id = b.UnitID) UnitNumber,
(select b2.building_name from buildings b2 where b.BuildingID = b2.id) BuildingName,
(select concat(t.first_name,' ',t.last_name) from tenants t where b.tenant_id = t.id) TenantNAme,
b.Currency,b.cost, b.ExpensDes
 from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
,a.ExpensDes,a.Currency,a.id
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id,
(select exl.expenseslines_name from expenseslines exl where exl.id = exi.expenses_lines_id) ExpensDes,
(select cur.currency from currencies cur where cur.id = exi.currency_id) Currency,id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date  >= startDate and b.date <= EndDate
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner)
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalLinkedTenants` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalLinkedTenants`(
	in PMCompanyID INT,
          in Limitn int,
    in offsetn int

)
BEGIN
SELECT id,(select concat(t.first_name,' ',t.last_name) from tenants t where tu.tenant_id = t.id) TenantNAme
,tu.unit_no,  (select b.building_name from buildings b where tu.building_id = b.id) BuildingName,
ifnull((select ow.name from owners ow where ow.id = tu.owner_id),'Owner Not Specified') OwnerName
FROM tenants_units tu
where tenant_id != 0
and (select concat(t.first_name,' ',t.last_name) from tenants t where tu.tenant_id = t.id) is not null
and pm_company_id = PMCompanyID
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOpenRequests12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOpenRequests12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
         in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -12 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4')
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOpenRequests1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOpenRequests1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -1 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4')
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOpenRequests6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOpenRequests6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -6 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4')
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOpenRequests9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOpenRequests9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
       in Limitn int,
    in offsetn int

 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -9 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4')
    limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOpenRequestsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOpenRequestsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in StartDate Date,
    in EndDate Date,
         in Limitn int,
    in offsetn int
 
)
BEGIN
select id,(select concat(t.first_name,' ',t.last_name) from tenants t where m.tenant_id = t.id) TenantNAme 
,m.unit_id,(select b.building_name from buildings b where m.building_id = b.id) BuildingName,
m.created_at,m.RequestFor, m.status 
FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner 
,(select rf.maitinance_request_name from maitinance_request_for rf where rf.id = m2.maintenance_request_id ) RequestFor
from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
and m.created_at >= startDate and m.created_at <= EndDate
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
and m.status in ('1','2','4')
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOverDuePayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOverDuePayments12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6')
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOverDuePayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOverDuePayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6')
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOverDuePayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOverDuePayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6')
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOverDuePayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOverDuePayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in Limitn int,
    in offsetn int

)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6')
 limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalOverDuePaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalOverDuePaymentsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in StartDate Date,
    in EndDate Date,
    in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
  and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6')
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalSetPayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalSetPayments12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
and p.status in (3)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalSetPayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalSetPayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
and p.status in (3,9)
 limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalSetPayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalSetPayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
and p.status in (3,9)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalSetPayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalSetPayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
and p.status in (3,9)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalSetPaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalSetPaymentsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
        in StartDate Date,
    in EndDate Date,
     in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
  and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
and p.status in (3,9)
limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalUpcomingPayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalUpcomingPayments12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalUpcomingPayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalUpcomingPayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
         in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalUpcomingPayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalUpcomingPayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
         in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1)
 limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalUpcomingPayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalUpcomingPayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
          in Limitn int,
    in offsetn int
)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1)
   limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `DrillDownTotalUpcomingPaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `DrillDownTotalUpcomingPaymentsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
           in StartDate Date,
    in EndDate Date,
     in Limitn int,
    in offsetn int

)
BEGIN
select 
id,(select concat(t.first_name,' ',t.last_name) from tenants t where p.tenant_id = t.id) TenantNAme,
 (select b.building_name from buildings b where p.building_id = b.id) BuildingName,
 (select tu.unit_no from tenants_units tu where tu.id = p.unit_id) UnitNumber,
 p.payment_type,p.payment_date,p.amount
FROM
(select id,pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
  and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1)
  limit Limitn offset offsetn;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalActiveAvalUnits` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalActiveAvalUnits`(
	in PMCompanyID int
)
BEGIN
SELECT count(id) as 'TotalActiveAvalUnits'
FROM contoliodev.avaliable_units where status = 1
and pm_company_id = PMCompanyID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalActiveBuildings` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalActiveBuildings`(
	IN PMCompanyID INT
)
BEGIN
	SELECT count(id) as 'TotalActiveBuildings'
FROM contoliodev.buildings where status = 1
and pm_company_id = PMCompanyID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalActiveUnits` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalActiveUnits`(
	in PMCompanyID INT
)
BEGIN
	SELECT count(id)  as 'TotalActiveUnits'
FROM contoliodev.tenants_units where status = 1
and pm_company_id = PMCompanyID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalClosedRequests12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalClosedRequests12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -12 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalClosedRequests1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalClosedRequests1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -1 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalClosedRequests6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalClosedRequests6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -6 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalClosedRequests9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalClosedRequests9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -9 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalClosedRequestsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalClosedRequestsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= startDate and m.created_at<= EndDate
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in (3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalContractsExpiring2m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalContractsExpiring2m`(
	in PMCompanyID int
)
BEGIN
SELECT count(id) as 'TotalContractsExpiring'
FROM contoliodev.contracts_tables where 
end_date <= date_add(SYSDATE(), interval 2 month) and end_date >= SYSDATE()
and pm_company_id = PMCompanyID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesAmount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesAmount12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(sum(b.cost),0) as TotalExpensesAmount from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -12 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesAmount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesAmount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(sum(b.cost),0) as TotalExpensesAmount from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -1 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesAmount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesAmount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(sum(b.cost),0) as TotalExpensesAmount from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -6 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesAmount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesAmount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(sum(b.cost),0) as TotalExpensesAmount from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -9 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesAmountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesAmountDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in StartDate Date,
    in EndDate Date
 
)
BEGIN
select ifnull(sum(b.cost),0) as TotalExpensesAmount from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date  >= startDate and b.date <= EndDate
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesCount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesCount12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(b.cost),0) as TotalExpenses from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -12 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesCount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesCount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(b.cost),0) as TotalExpenses from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -1 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesCount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesCount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(Count(b.cost),0) as TotalExpenses from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -6 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesCount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesCount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(Count(b.cost),0) as TotalExpenses from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date >= date_add(SYSDATE(), interval -9 month)
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalExpensesCountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalExpensesCountDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
      in StartDate Date,
    in EndDate Date
 
)
BEGIN
select ifnull(Count(b.cost),0) as TotalExpenses from
(select a.cost,a.date,a.expenses_id, a.PMcompay,a.UnitID,a.BuildingID, a.tenant_id 
,(select owner_id from contoliodev.tenants_units tu where tu.id = a.unitID) Owner
from
(SELECT exi.cost,exi.date,exi.expenses_id 
,(select ex.pm_company_id from contoliodev.expenses ex where ex.id = exi.expenses_id) PMcompay
,(select ex.unit_id from contoliodev.expenses ex where ex.id = exi.expenses_id) UnitID
,(select ex.building_id from contoliodev.expenses ex where ex.id = exi.expenses_id) BuildingID
,(select ex.tenant_id from contoliodev.expenses ex where ex.id = exi.expenses_id) tenant_id
FROM contoliodev.expenses_items exi) a)b
 where b.PMcompay = PMCompanyID
 and b.date  >= startDate and b.date <= EndDate
  and b.BuildingID = ifnull(BuildingID,b.BuildingID)
  and b.tenant_id = ifnull(TenantID,b.tenant_id)
  and b.owner = ifnull(OwnerID,b.owner);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalLinkedTenants` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalLinkedTenants`(
	in PMCompanyID INT
)
BEGIN
	SELECT count(id) TotalLinkedTenants FROM contoliodev.tenants_units
where tenant_id != 0
and pm_company_id = PMCompanyID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOpenRequests12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOpenRequests12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0)  as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -12 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOpenRequests1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOpenRequests1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -1 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOpenRequests6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOpenRequests6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -6 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOpenRequests9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOpenRequests9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
 
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= date_add(SYSDATE(), interval -9 month)
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOpenRequestsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOpenRequestsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(count(m.id),0) as TotalPaymentsAmount FROM(
SELECT id,pm_company_id,tenant_id,unit_id,building_id,status,created_at,
(select owner_id  from contoliodev.tenants_units tu where m2.unit_id = tu.id) owner from
 contoliodev.maintance_requests m2) m
 where m.pm_company_id = PMCompanyID
 and m.created_at >= startDate and m.created_at<= EndDate
  and m.building_id = ifnull(BuildingID,m.building_id)
  and m.tenant_id = ifnull(TenantID,m.tenant_id)
  and m.owner = ifnull(OwnerID,m.owner)
  and m.status in ('1','2','4');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePayments12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
   and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsCount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsCount12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsCount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsCount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsCount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsCount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsCount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsCount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
   and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsCountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsCountDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalOverDuePaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalOverDuePaymentsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in ('4','5','6');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPayments12m`(
	in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int

)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsCount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsCount12m`(
	in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsCount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsCount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int

)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsCount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsCount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsCount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsCount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= date_add(SYSDATE(), interval -9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsCountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsCountDRange`(
	in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalSetPaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalSetPaymentsDRange`(
	in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int,
     in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
  and p.status in (3,9);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPayments12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPayments12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPayments1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPayments1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPayments6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPayments6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPayments9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPayments9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsCount12m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsCount12m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 12 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsCount1m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsCount1m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 1 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsCount6m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsCount6m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 6 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsCount9m` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsCount9m`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
    in TenantID int
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= SYSDATE() and  p.payment_date <= date_add(SYSDATE(), interval 9 month)
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsCountDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsCountDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
      in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(Count(p.amount),0) as TotalPayments FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TotalUpcomingPaymentsDRange` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`BeastDevAdminSQL`@`%` PROCEDURE `TotalUpcomingPaymentsDRange`(
in PMCompanyID int,
    in BuildingID int,
    in OWnerID int,
      in TenantID int,
    in StartDate Date,
    in EndDate Date
)
BEGIN
select ifnull(sum(p.amount),0) as TotalPaymentsAmount FROM
(select pm_company_id,tenant_id,building_id,payment_type,amount,payment_date,status,unit_id,
(select owner_id  from contoliodev.tenants_units tu where p2.unit_id = tu.id) owner from
 contoliodev.payments p2 )p
 where p.pm_company_id = PMCompanyID
 and p.payment_date >= StartDate and p.payment_date <= EndDate
  and p.building_id = ifnull(BuildingID,p.building_id)
  and p.tenant_id = ifnull(TenantID,p.tenant_id)
  and p.owner = ifnull(OwnerID,p.owner)
 and p.status in (1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-04-26 18:42:09
