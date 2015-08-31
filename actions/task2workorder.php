                                                                                                                                                                                                                                         <?php
/**
* Copy existing TASK to WO
*  
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage task2wo
* @filesource
*/
require("../includes/config_mycmms.inc.php");

switch ($_REQUEST['STEP']) {
case "1": {
    $DB=DBC::get();
    $param_TASKNUM=$_REQUEST['TASKNUM'];
    $param_WONUM=$_REQUEST['WONUM'];
            DebugBreak();
    if ($_REQUEST['new']=="on") {
        $bool_New_Workorder=true;
    } else {
        $bool_New_Workorder=false;
    }
/**
* When NEW WO, all data is copied into the WO
* Else only the woop,... data        
*/
        if ($bool_New_Workorder) {
            $param_EQNUM=$_REQUEST['EQNUM'];
            try {
                $DB->beginTransaction();
                DBC::execute("INSERT INTO wo (WONUM,EQNUM,TASKNUM,TASKDESC,TEXTS_A,TEXTS_B,WOTYPE,PRIORITY,ORIGINATOR,WOSTATUS,EXPENSE,REQUESTDATE,APPROVEDDATE,APPROVEDBY,SCHEDSTARTDATE) 
                SELECT NULL,:eqnum,task.TASKNUM,DESCRIPTION,TEXTS_A,'Feedback:','PPM',2,'CMMS','P','MAINT',NOW(),NOW(),'CMMS',NEXTDUEDATE FROM taskeq LEFT JOIN task ON taskeq.TASKNUM=task.TASKNUM WHERE taskeq.TASKNUM=:tasknum",
                array("tasknum"=>$param_TASKNUM,"eqnum"=>$param_EQNUM));
                $param_WONUM=DBC::fetchcolumn("SELECT LAST_INSERT_ID()",0); # Replace 
                $DB->commit();
            } catch (Exception $e) {
                $DB->rollBack();
                PDO_log($e);
            }
        } else {
            $param_EQNUM=DBC::fetchcolumn("SELECT EQNUM FROM wo WHERE WONUM={$_REQUEST['WONUM']}",0);  
            DBC::execute("DELETE FROM woop WHERE WONUM=:wonum",array("wonum"=>$param_WONUM));
            DBC::execute("DELETE FROM wocraft WHERE WONUM=:wonum",array("wonum"=>$param_WONUM));
            DBC::execute("DELETE FROM wop WHERE WONUM=:wonum",array("wonum"=>$param_WONUM));
            DBC::execute("DELETE FROM document_links WHERE WONUM=:wonum",array("wonum"=>$param_WONUM));
            DBC::execute("UPDATE wo SET TASKNUM=:tasknum WHERE WONUM=:wonum",array("wonum"=>$param_WONUM,"tasknum"=>$param_TASKNUM));
        }
        try {
            $DB->beginTransaction();
            DBC::execute("INSERT INTO woop (WONUM,OPNUM,OPDESC) SELECT :wonum,OPNUM,OPDESC FROM tskop WHERE TASKNUM=:tasknum",array("wonum"=>$param_WONUM,"tasknum"=>$param_TASKNUM));
            DBC::execute("INSERT INTO wocraft (WONUM,OPNUM,CRAFT,TEAM,ESTHRS) SELECT :wonum,OPNUM,CRAFT,TEAM,ESTHRS FROM tskcraft WHERE TASKNUM=:tasknum",array("wonum"=>$param_WONUM,"tasknum"=>$param_TASKNUM));
            DBC::execute("INSERT INTO wop (WONUM,ITEMNUM,QTYREQD) SELECT :wonum,ITEMNUM,QTYREQD FROM tskparts WHERE TASKNUM=:tasknum",array("wonum"=>$param_WONUM,"tasknum"=>$param_TASKNUM));
            DBC::execute("INSERT INTO document_links (FILENAME,WONUM) SELECT FILENAME,:wonum FROM document_links WHERE TASKNUM=:tasknum",array("wonum"=>$param_WONUM,"tasknum"=>$param_TASKNUM));
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
    
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("tasknum",$_REQUEST['TASKNUM']);
    $tpl->assign("wonum",$_REQUEST['WONUM']);
    $tpl->assign("new",$_REQUEST['new']);
    if (is_null($e)) {
        $error="OK";
    } else {
        $error=$e->getMessage();
    }
    $tpl->assign("error",$error);

    $tpl->display_error("task2workorder_end.tpl");    
    break;
}   
default: {
    require("setup.php");
    require("_wikihelp.php");

    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->display_error("task2workorder_form.tpl");
    break;
} // EO default
} // EO switch
?>
