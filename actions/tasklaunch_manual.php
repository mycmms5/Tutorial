<?php
/** 
* Standalone TASK launching, this script does 3 sequential tasks:
* - Sets the NEXTDUEDATE in every Task (set_nextduedate)
* - Get a list of all active tasks (ACTIVE=0 and NEXTDUEDATE<NOW and no active WO (get_available_tasks)
*
* @author  Werner Huysmans 
* @access  public
* @package ppm
* @subpackage tasklaunch
* @filesource
* CVS
* $Id: tasklaunch_manual.php,v 1.3 2013/05/26 07:00:19 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/tasklaunch_manual.php,v $
* $Log: tasklaunch_manual.php,v $
* Revision 1.3  2013/05/26 07:00:19  werner
* Launch with Preview and ONLY active tasks
*
* Revision 1.2  2013/04/17 05:59:41  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");  
require("setup.php");
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->assign("stylesheet",STYLE_PATH.CSS_SMARTY);
$version=__FILE__." :V5.0 Build 20150808";
$tpl->assign("version",$version); 

switch ($_REQUEST['STEP']) {
    case "1": { // Limit date set, calculating Next Due Dates
        $DB=DBC::get();
        try {
            $DB->beginTransaction();
            DBC::execute("UPDATE taskeq SET NEXTDUEDATE=ADDDATE(LASTPERFDATE,INTERVAL NUMOFDATE DAY) WHERE NEXTDUEDATE<NOW() AND SCHEDTYPE='F'",array());
            DBC::execute("UPDATE taskeq SET NEXTDUEDATE=ADDDATE(NEXTDUEDATE,INTERVAL (CEIL(DATEDIFF(NOW(),NEXTDUEDATE)/NUMOFDATE)*NUMOFDATE) DAY) WHERE NEXTDUEDATE<NOW() AND SCHEDTYPE='X'",array());
            $DB->commit(); 
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);                   
        } 
        $_SESSION['LIMIT']=$_REQUEST['LIMIT'];
        $result=$DB->query("SELECT task.TASKNUM AS 'tasknum',EQNUM AS 'eqnum',DESCRIPTION AS 'description',NEXTDUEDATE AS 'next',ACTIVE AS 'active' FROM task LEFT JOIN taskeq ON task.TASKNUM=taskeq.TASKNUM WHERE NEXTDUEDATE<ADDDATE(NOW(),INTERVAL {$_REQUEST['LIMIT']} DAY) AND taskeq.EQNUM<>'CMMS' AND taskeq.WONUM IS NULL  AND taskeq.ACTIVE=1 AND taskeq.SCHEDTYPE IN ('F','X')");
        if ($result) {
            $tasks=$result->fetchAll(PDO::FETCH_ASSOC);
        }
        $tpl->assign("tasks",$tasks);
        $tpl->assign("step","STEP1");
        $tpl->display_error("action/tasklaunch_manual.tpl");
        break;
    }   // EO STEP1
    case "2": { // Presenting marked tasks 
        $DB=DBC::get();
        DBC::execute("UPDATE taskeq SET LAUNCH=-1",array());    // Reset
        for ($c=0; $c < sizeof($_REQUEST['taskid']); $c++) {
            $taskid = split(":",$_REQUEST['taskid'][$c]);
            DBC::execute("UPDATE taskeq SET LAUNCH=1 WHERE TASKNUM=:tasknum AND EQNUM=:eqnum",array("tasknum"=>$taskid[0],"eqnum"=>$taskid[1]));
        }
        $result=$DB->query("SELECT task.TASKNUM AS 'tasknum',EQNUM AS 'eqnum',DESCRIPTION AS 'description' FROM task LEFT JOIN taskeq ON task.TASKNUM=taskeq.TASKNUM WHERE NEXTDUEDATE < ADDDATE(NOW(),INTERVAL {$_SESSION['LIMIT']} DAY) AND taskeq.LAUNCH=1 AND taskeq.WONUM IS NULL");
        $tasks=$result->fetchAll(PDO::FETCH_ASSOC);
        
        $tpl->assign("tasks",$tasks);
        $tpl->assign("step","STEP2");
        $tpl->display_error("action/tasklaunch_manual.tpl");
        break;
    }
    case "3":   {
        $DB=DBC::get();
        # Find tasks that must be launched
        $result=$DB->query("SELECT task.TASKNUM,EQNUM,DESCRIPTION,TEXTS_A,NEXTDUEDATE FROM taskeq LEFT JOIN task ON taskeq.TASKNUM=task.TASKNUM WHERE WONUM is NULL AND LAUNCH=1");
        if ($result) {
            foreach($result->fetchAll(PDO::FETCH_OBJ) as $task) {
            try {
                $DB->beginTransaction();
                DBC::execute("INSERT INTO wo (WONUM,EQNUM,TASKNUM,TASKDESC,TEXTS_A,WOTYPE,PRIORITY,ORIGINATOR,WOSTATUS,EXPENSE,REQUESTDATE,SCHEDSTARTDATE,APPROVEDDATE,APPROVEDBY) VALUES (NULL,:eqnum,:tasknum,:taskdesc,:texts_a,'PPM',2,'CMMS','P','MAINT',NOW(),:schedstartdate,NOW(),'CMMS')",array("tasknum"=>$task->TASKNUM,"eqnum"=>$task->EQNUM,"taskdesc"=>$task->DESCRIPTION,"texts_a"=>$task->TEXTS_A,"schedstartdate"=>$task->NEXTDUEDATE));
                $new_wo=DBC::fetchcolumn("SELECT LAST_INSERT_ID()",0);
                DBC::execute("INSERT INTO woop (WONUM,OPNUM,OPDESC) SELECT :wonum,OPNUM,OPDESC FROM tskop WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$task->TASKNUM));
                DBC::execute("INSERT INTO wocraft (WONUM,OPNUM,CRAFT,TEAM,ESTHRS) SELECT :wonum,OPNUM,CRAFT,TEAM,ESTHRS FROM tskcraft WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$task->TASKNUM));
                DBC::execute("INSERT INTO wop (WONUM,ITEMNUM,QTYREQD) SELECT :wonum,ITEMNUM,QTYREQD FROM tskparts WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$task->TASKNUM));
                DBC::execute("INSERT INTO wodocu (WONUM,FILENAME,DESCRIPTION) SELECT :wonum,FILENAME,DESCRIPTION FROM taskdocu WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$task->TASKNUM));
                DBC::execute("UPDATE taskeq SET WONUM=:wonum WHERE TASKNUM=:tasknum AND EQNUM=:eqnum",array("wonum"=>$new_wo,"eqnum"=>$task->EQNUM,"tasknum"=>$task->TASKNUM));;
                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                PDO_log($e);
            } // EO try
        } // EO foreach
        } // EO result
        
        $result=$DB->query("SELECT task.TASKNUM AS 'tasknum',EQNUM AS 'eqnum',DESCRIPTION AS 'description',taskeq.WONUM FROM task LEFT JOIN taskeq ON task.TASKNUM=taskeq.TASKNUM WHERE taskeq.LAUNCH=1");

        $tasks=$result->fetchAll(PDO::FETCH_ASSOC);
        
        $tpl->assign("tasks",$tasks);
        $tpl->assign("step","END");
        $tpl->display_error("action/tasklaunch_manual.tpl");
        break;
    }
    default: {
        $tpl->assign("step","FORM"); 
        $tpl->display_error("action/tasklaunch_manual.tpl");
    }
}
?>
 
