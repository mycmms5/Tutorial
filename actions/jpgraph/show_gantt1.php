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
$gantt->gantt_title=_("Week Planning");
$gantt->init("HDW");
// Adding activities
$result=$DB->query("SELECT CONCAT(WONUM,':',LEFT(TASKDESC,25)) AS 'WO',SCHEDSTARTDATE AS 'START',ADDDATE(SCHEDSTARTDATE,1) AS 'END',MID(EQNUM,1) AS 'RESS' FROM wo WHERE WOSTATUS='PL' AND SCHEDSTARTDATE BETWEEN ADDDATE(NOW(),-3) AND ADDDATE(NOW(),10) LIMIT 0,100");
if ($result) {
    $wo=array();
    $i=0;
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $wo) {
        $gantt->add_activity($i,$wo["WO"],$wo["START"],$wo["END"],$wo["RESS"]);
        $i++;   // Increment
    }
}
// Mark today
$gantt->set_now_marker();
$gantt->show_GANTT();
?>
