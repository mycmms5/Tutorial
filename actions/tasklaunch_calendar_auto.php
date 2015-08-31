<?php
/** 
* PPM tasklauncher for calendar tasks:
* - Preview defined in $preview
* - Equipment-range defined in $eqnum
* 
* @author  Werner Huysmans 
* @access  public
* @package ppm
* @subpackage tasklaunch
* @filesource
* CVS
* $Id: tasklaunch_calendar_auto.php,v 1.2 2013/04/17 06:00:47 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/tasklaunch_calendar_auto.php,v $
* $Log: tasklaunch_calendar_auto.php,v $
* Revision 1.2  2013/04/17 06:00:47  werner
* Inserted CVS variables Id,Source and Log
*
*/
set_time_limit(0);  // No time limit...
require("../includes/config_mycmms.inc.php");  
unset($_SESSION['PDO_ERROR']);
require("setup.php");
$preview="7 DAY";
/**
* Step 1: Get all tasks 
*/
$DB=DBC::get();
$result=$DB->query("SELECT pc.TASKNUM AS 'TASKNUM',pc.EQNUM AS 'EQNUM',t.DESCRIPTION AS 'description',pc.PLANDATE FROM ppmcalendar pc LEFT JOIN task t ON pc.TASKNUM=t.TASKNUM WHERE PLANDATE BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL {$preview}) AND pc.EQNUM LIKE '%' AND pc.WONUM IS NULL");

if ($result->rowCount() > 0) {
/**
* Step 2: Launch all found tasks
*/
    foreach($result->fetchAll(PDO::FETCH_ASSOC) as $task)
        try{
            $DB->beginTransaction();
            $pTASKNUM=$task['TASKNUM']; $pEQNUM=$task['EQNUM']; $pPLANDATE=$task['PLANDATE'];
            DBC::execute("INSERT INTO wo (WONUM,EQNUM,TASKNUM,TASKDESC,TEXTS_A,TEXTS_B,WOTYPE,PRIORITY,ORIGINATOR,WOSTATUS,EXPENSE,REQUESTDATE,APPROVEDDATE,APPROVEDBY,SCHEDSTARTDATE) SELECT NULL,EQNUM,task.TASKNUM,DESCRIPTION,TEXTS_A,'Feedback:','PPM',2,'CMMS','P','MAINT',NOW(),NOW(),'CMMS',:plandate FROM taskeq LEFT JOIN task ON taskeq.TASKNUM=task.TASKNUM WHERE taskeq.TASKNUM=:tasknum AND taskeq.EQNUM=:eqnum",array("tasknum"=>$pTASKNUM,"eqnum"=>$pEQNUM,"plandate"=>$pPLANDATE));
            $new_wo=DBC::fetchcolumn("SELECT LAST_INSERT_ID()",0);
            DBC::execute("INSERT INTO woop (WONUM,OPNUM,OPDESC) SELECT :wonum,OPNUM,OPDESC FROM tskop WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO wocraft (WONUM,OPNUM,CRAFT,TEAM,ESTHRS) SELECT :wonum,OPNUM,CRAFT,TEAM,ESTHRS FROM tskcraft WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO wop (WONUM,ITEMNUM,QTYREQD) SELECT :wonum,ITEMNUM,QTYREQD FROM tskparts WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO document_links (FILENAME,WONUM) SELECT FILENAME,:wonum FROM document_links WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("UPDATE taskeq SET WONUM=:wonum WHERE TASKNUM=:tasknum AND EQNUM=:eqnum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM,"eqnum"=>$pEQNUM));
            DBC::execute("UPDATE ppmcalendar SET WONUM=:wonum WHERE TASKNUM=:tasknum AND EQNUM=:eqnum AND PLANDATE=:plandate",array("wonum"=>$new_wo,"eqnum"=>$pEQNUM,"tasknum"=>$pTASKNUM,"plandate"=>$pPLANDATE));
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
} else {
    $message="No tasks found!";
}

$tpl=new smarty_mycmms();
$tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
$tpl->assign("tasks",$DB->query("SELECT pc.TASKNUM AS 'tasknum',pc.EQNUM AS 'eqnum',t.DESCRIPTION AS 'description',pc.PLANDATE,pc.WONUM AS 'WONUM' FROM ppmcalendar pc LEFT JOIN task t ON pc.TASKNUM=t.TASKNUM WHERE PLANDATE BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL {$preview})"));
$tpl->display_error("tasklaunch_calendar_auto_end.tpl");
?>
 
