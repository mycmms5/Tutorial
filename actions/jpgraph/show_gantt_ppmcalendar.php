<?php
/**
* Utility to show PPM Plan
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

$gantt= new JPG_GANTT();
$gantt->gantt_title=_("PPM Plan");
$gantt->init("DWM");
// Adding activities
$result=$DB->query("SELECT TASKNUM AS 'TASK',PLANDATE AS 'START',ADDDATE(PLANDATE,1) AS 'END','LOAD' AS 'RESS' FROM ppmcalendar WHERE YEAR(PLANDATE)=2011 GROUP BY EQNUM,PLANDATE LIMIT 200");
if ($result) {
    $tasks=array();
    $i=0;
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $tasks) {
        $gantt->add_activity($i,$tasks["TASK"],$tasks["START"],$tasks["END"],$tasks["RESS"]);
        $i++;   // Increment
    }
}
// Mark today
$gantt->set_now_marker();
$gantt->show_GANTT();
?>
