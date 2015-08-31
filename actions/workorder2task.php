<?php
/** 
* Copy WO content to Task
* 
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage task2wo
* @filesource
* @version $Id: workorder2task.php,v 1.2 2013/11/04 07:47:52 werner Exp $121224
*/
require("../includes/config_mycmms.inc.php");

switch ($_REQUEST['STEP']) {
case "1": { # Present the WO data before copying
    $DB=DBC::get();
    # Data collection
    $result=$DB->query("SELECT wo.*,equip.DESCRIPTION FROM wo 
        LEFT JOIN equip ON wo.EQNUM=equip.EQNUM 
        WHERE wo.WONUM={$_REQUEST['WONUM']}");
    $wo_main_data=$result->fetch(PDO::FETCH_ASSOC);
    $result=$DB->query("SELECT dl.FILENAME,dd.FILEDESCRIPTION FROM document_links dl LEFT JOIN document_descriptions dd ON dl.FILENAME=dd.FILENAME WHERE WONUM='{$_REQUEST['WONUM']}'");
    $wo_docs=$result->fetch(PDO::FETCH_ASSOC);
    $work=$DB->query("SELECT woop.OPNUM,woop.OPDESC,CRAFT,TEAM,ESTHRS FROM woop LEFT JOIN wocraft WC ON woop.WONUM=WC.WONUM AND woop.OPNUM=WC.OPNUM WHERE woop.WONUM={$_REQUEST['WONUM']}",PDO::FETCH_ASSOC);
    $spares=$DB->query("SELECT wop.ITEMNUM,DESCRIPTION,QTYREQD,LOCATION,QTYUSED FROM wop LEFT JOIN invy ON wop.ITEMNUM=invy.ITEMNUM LEFT JOIN stock ON wop.ITEMNUM=stock.ITEMNUM WHERE WONUM={$_REQUEST['WONUM']}",PDO::FETCH_ASSOC);

    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("wo_main_data",$wo_main_data);
    $tpl->assign("wo_docs",$wo_docs);
    $tpl->assign("work",$work);
    $tpl->assign("spares",$spares);
    $tpl->display_error("workorder2task_preview.tpl");
    break;
} // EO STEP1   
case "2": {
    $DB=DBC::get();
    # Check if task is empty or only a dummy
    $activePPMtask=DBC::fetchcolumn("SELECT COUNT(*) FROM TASKEQ WHERE TASKNUM='{$_REQUEST['TASKNUM']}' AND EQNUM<>'CMMS'",0);
    $param_WONUM=$_REQUEST['WONUM'];
    $param_TASKNUM=$_REQUEST['TASKNUM'];
    if (intval($activePPMtask) > 0) { // An active task with this name exists!
        $bool_ActiveTask_exists=true; 
    } else { $bool_ActiveTask_exists=false; }
    try {
        $DB->beginTransaction();
        DBC::execute("UPDATE wo SET TEXTS_A='No Methods' WHERE WONUM=:wonum AND TEXTS_A IS NULL",array("wonum"=>$param_WONUM));
        if (!$bool_ActiveTask_exists) {
            DBC::execute("DELETE FROM task WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
            DBC::execute("DELETE FROM taskeq WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
            DBC::execute("INSERT INTO task (TASKNUM,DESCRIPTION,WOTYPE,TEXTS_A,LASTEDITDATE) SELECT :tasknum,:description,'PPM',TEXTS_A,NOW() FROM wo WHERE WONUM=:wonum",array("tasknum"=>$param_TASKNUM,"description"=>$pDESCRIPTION,"wonum"=>$param_WONUM));
            DBC::execute("INSERT INTO taskeq (TASKNUM,EQNUM,LASTPERFDATE,SCHEDTYPE,DATEUNIT,NUMOFDATE) VALUES (:tasknum,'CMMS','20000101','F','D',30)",array("tasknum"=>$param_TASKNUM));
        } else {
            DBC::execute("UPDATE task SET TEXTS_A=(SELECT TEXTS_A FROM wo WHERE WONUM=:wonum) WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM,"wonum"=>$param_WONUM));
        }
        DBC::execute("DELETE FROM tskop WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
        DBC::execute("DELETE FROM tskcraft WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
        DBC::execute("DELETE FROM tskparts WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
        DBC::execute("DELETE FROM document_links WHERE TASKNUM=:tasknum",array("tasknum"=>$param_TASKNUM));
        # New TASK
        DBC::execute("INSERT INTO tskop (TASKNUM,OPNUM,OPDESC) SELECT :tasknum,OPNUM,OPDESC FROM woop WHERE WONUM=:wonum",array("tasknum"=>$param_TASKNUM,"wonum"=>$param_WONUM));
        DBC::execute("INSERT INTO tskcraft (TASKNUM,OPNUM,CRAFT,TEAM,ESTHRS) SELECT :tasknum,OPNUM,CRAFT,TEAM,ESTHRS FROM wocraft WHERE WONUM=:wonum",array("tasknum"=>$param_TASKNUM,"wonum"=>$param_WONUM));
        DBC::execute("INSERT INTO tskparts (TASKNUM,ITEMNUM,QTYREQD) SELECT DISTINCT :tasknum,ITEMNUM,QTYREQD FROM wop WHERE WONUM=:wonum AND QTYREQD IS NOT NULL",array("tasknum"=>$param_TASKNUM,"wonum"=>$param_WONUM));
        DBC::execute("INSERT INTO document_links (FILENAME,TASKNUM) SELECT FILENAME,:tasknum FROM document_links WHERE WONUM=:wonum",array("tasknum"=>$param_TASKNUM,"wonum"=>$param_WONUM));
        $DB->commit();
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    } // EO try
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("tasknum",$_REQUEST['TASKNUM']);
    $tpl->assign("wonum",$_REQUEST['WONUM']);
    if (is_null($e)) {
        $error="OK";
    } else {
        $error=$e->getMessage();
    }
    $tpl->assign("error",$error);
    $tpl->display_error("workorder2task_end.tpl");    
    break;
} // EO STEP2    
default: {
    require("setup.php");
    require("_wikihelp.php");
    
    $tpl=new smarty_mycmms();
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->display_error("workorder2task_form.tpl");
}// EO default
} // EO switch
?>
