<?php
/**
* Automatic task launching, this script does 3 sequential tasks:
* - Sets the NEXTDUEDATE in every Task (TXID_6016)
* - Get a list of all active tasks (ACTIVE=0 and NEXTDUEDATE<NOW and no active WO (get_available_tasks)
* 
* @author  Werner Huysmans 
* @access  public
* @package ppm
* @subpackage tasklaunch
* @filesource
* CVS
* $Id: tasklaunch_auto.php,v 1.2 2013/04/17 05:59:40 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/tasklaunch_auto.php,v $
* $Log: tasklaunch_auto.php,v $
* Revision 1.2  2013/04/17 05:59:40  werner
* Inserted CVS variables Id,Source and Log
*
*/
set_time_limit(0);  // No time limit...
require("../includes/config_mycmms.inc.php"); 
/** 
* Open a LOG file
*/
$fh=fopen(LAUNCH_LOG,"a+");
$DB=DBC::get();
/** 
* Sequence is straightforward:
* - Make sure all dates are refreshed 
*/
try {
    $DB->beginTransaction();
    DBC::execute("UPDATE taskeqx SET NEXTDUEDATE=ADDDATE(LASTPERFDATE,INTERVAL NUMOFDATE DAY) WHERE NEXTDUEDATE<NOW() AND SCHEDTYPE='F'",array());
    DBC::execute("UPDATE taskeq SET NEXTDUEDATE=ADDDATE(NEXTDUEDATE,INTERVAL (CEIL(DATEDIFF(NOW(),NEXTDUEDATE)/NUMOFDATE)*NUMOFDATE) DAY) WHERE NEXTDUEDATE<NOW() AND SCHEDTYPE='X'",array());
    $DB->commit(); 
} catch (Exception $e) {
    $DB->rollBack();
    PDO_log($e);                   
} 
/**
* Make list of all due work...
*/
$result=$DB->query("SELECT task.TASKNUM AS 'TASKNUM',EQNUM AS 'EQNUM' FROM task LEFT JOIN taskeq ON task.TASKNUM=taskeq.TASKNUM WHERE ACTIVE=1 AND NEXTDUEDATE<ADDDATE(NOW(),INTERVAL 7 DAY) AND taskeq.WONUM IS NULL AND SCHEDTYPE IN ('F','X')");
fwrite($fh,"Log AutoLauncher:".now2string(true)."\n");
$i=0;
if ($result) {
    foreach($result->fetchAll(PDO::FETCH_OBJ) as $row) {
        $pTASKNUM=$row->TASKNUM; $pEQNUM=$row->EQNUM;
        try{
            $DB->beginTransaction();
            $pTASKNUM=$row->TASKNUM; $pEQNUM=$row->EQNUM;
            DBC::execute("INSERT INTO wo (WONUM,EQNUM,TASKNUM,TASKDESC,TEXTS_A,TEXTS_B,WOTYPE,PRIORITY,ORIGINATOR,WOSTATUS,EXPENSE,REQUESTDATE,APPROVEDDATE,APPROVEDBY,SCHEDSTARTDATE) SELECT NULL,EQNUM,task.TASKNUM,DESCRIPTION,TEXTS_A,'Feedback:','PPM',2,'CMMS','P','MAINT',NOW(),NOW(),'CMMS',NEXTDUEDATE FROM taskeq LEFT JOIN task ON taskeq.TASKNUM=task.TASKNUM WHERE taskeq.TASKNUM=:tasknum AND taskeq.EQNUM=:eqnum",array("tasknum"=>$pTASKNUM,"eqnum"=>$pEQNUM));
            $new_wo=DBC::fetchcolumn("SELECT LAST_INSERT_ID()",0);
            DBC::execute("INSERT INTO woop (WONUM,OPNUM,OPDESC) SELECT :wonum,OPNUM,OPDESC FROM tskop WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO wocraft (WONUM,OPNUM,CRAFT,TEAM,ESTHRS) SELECT :wonum,OPNUM,CRAFT,TEAM,ESTHRS FROM tskcraft WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO wop (WONUM,ITEMNUM,QTYREQD) SELECT :wonum,ITEMNUM,QTYREQD FROM tskparts WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("INSERT INTO document_links (FILENAME,WONUM) SELECT FILENAME,:wonum FROM document_links WHERE TASKNUM=:tasknum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM));
            DBC::execute("UPDATE taskeq SET WONUM=:wonum WHERE TASKNUM=:tasknum AND EQNUM=:eqnum",array("wonum"=>$new_wo,"tasknum"=>$pTASKNUM,"eqnum"=>$pEQNUM));
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
        fwrite($fh,$i.":".$row->TASKNUM." for machine ".$row->EQNUM." into ".$new_wo."\n");
        $i++;
    }
} else {
    fwrite($fh,"No new tasks found");
}
fwrite($fh,"Found ".$i." tasks to launch\n");
fclose($fh);
# Blocked
if (false) {
    $fh=fopen(LAUNCH_LOG,"r");
    $content=fread($fh,filesize(LAUNCH_LOG));
    echo "<pre>".$content."</pre>";
}  

# data
$result=$DB->query("SELECT wo.*,e.DESCRIPTION FROM wo LEFT JOIN equip e ON wo.EQNUM=e.EQNUM WHERE REQUESTDATE > ADDDATE(NOW(),INTERVAL -1 DAY) AND TASKNUM<>'NONE'");
$data=$result->fetchAll(PDO::FETCH_ASSOC);
# labels
require("setup.php");
$tpl=new smarty_mycmms();
$tpl->debugging=false;
$tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
$tpl->assign('data',$data);
$tpl->display_error("tasklaunch_auto.tpl");

set_time_limit(30);
?>

