<?php
/**
* Utility to show GANTT
* 
* @author  Werner Huysmans 
* @access  public
* @package JPGRAPH
* @subpackage GANTT
* @filesource
*/
require_once("../includes/config_mycmms.inc.php");
require(CMMS_LIB."/class_jpgraph_gantt.php");
$DB=DBC::get();

$gantt=new JPG_GANTT();
$gantt->gantt_title="Launched PPM works";
$gantt->init("DWM");
// Adding activities
$result=$DB->query("SELECT CONCAT(WONUM,':',LEFT(TASKDESC,20)) AS 'WO',SCHEDSTARTDATE AS 'START',ADDDATE(SCHEDSTARTDATE,1) AS 'END',EQNUM AS 'RESS' FROM wo WHERE WOSTATUS IN ('P','PL') AND EQNUM LIKE '{$_SESSION['dept']}%' AND TASKNUM<>'NONE' AND YEAR(SCHEDSTARTDATE)>2009");
if ($result) {
    $wo=array();
    $i=0;
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $wo) {
        $gantt->add_activity($i,$wo["WO"],$wo["START"],$wo["END"],$wo["RESS"]);
        $i++;   // Increment
    }
}
$gantt->show_GANTT();
?>
