<?PHP
/** 
* Quickly planning prepared workorders 
* 
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage planning
* @filesource
* CVS
* $Id: weekplanning.php,v 1.1 2013/08/30 14:48:23 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/weekplanning.php,v $
* $Log: weekplanning.php,v $
* Revision 1.1  2013/08/30 14:48:23  werner
* Base is global_weekplanning extended with SUM(wocraft) and SUM(woe)
*
* Revision 1.8  2013/08/23 08:28:38  werner
* CVS parameters
*
*/
require("../includes/config_mycmms.inc.php");
require("setup.php");
require("lib_queries.php");
$DB=DBC::get();

switch ($_REQUEST['STEP']) {
case "1": { 
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('stylesheet_calendar',STYLE_PATH."/".CSS_CALENDAR);
    # labels
    $tpl->assign('eqline',$_REQUEST['EQNUM']);
    $tpl->assign('active_wo',$_REQUEST['ID']);
    # data from WO to be planned
    $tpl->assign('data',$DB->query("SELECT wo.WONUM,EQNUM,TASKDESC,WOSTATUS,PRIORITY,SCHEDSTARTDATE,ASSIGNEDTECH,PERIOD(SCHEDSTARTDATE) AS 'PLANWEEK',SUM(TEAM*ESTHRS) AS 'PLANHOURS' FROM wo LEFT JOIN wocraft ON wo.WONUM=wocraft.WONUM WHERE WOSTATUS IN('P','PL') AND EQNUM LIKE '{$_REQUEST['EQNUM']}%' GROUP BY wo.WONUM,EQNUM,TASKDESC,WOSTATUS,SCHEDSTARTDATE",PDO::FETCH_ASSOC));
    # Data from WO already planned 
    $tpl->assign('wo_planned',$DB->query("SELECT 'REGULAR' AS 'TYPE',PERIOD(SCHEDSTARTDATE) AS 'PLANWEEK',SUM(TEAM*ESTHRS) AS 'PLANHOURS' FROM wo LEFT JOIN wocraft ON wo.WONUM=wocraft.WONUM WHERE WOSTATUS='PL' AND TASKNUM='NONE' AND EQNUM LIKE '{$_REQUEST['EQNUM']}%' GROUP BY PLANWEEK,TYPE 
UNION
SELECT 'PPM' AS 'TYPE',PERIOD(SCHEDSTARTDATE) AS 'PLANWEEK',SUM(TEAM*ESTHRS) AS 'PLANHOURS' FROM wo LEFT JOIN wocraft ON wo.WONUM=wocraft.WONUM WHERE WOSTATUS='PL' AND TASKNUM<>'NONE' AND EQNUM LIKE '{$_REQUEST['EQNUM']}%'     
GROUP BY PLANWEEK,TYPE",PDO::FETCH_ASSOC));
    if ($_REQUEST['1CLICK']=='on') {
        $tpl->display_error("weekplanning_list2.tpl");   
    } else {
        $tpl->display_error("weekplanning_list1.tpl");
    }
    break;
} // EO STEP1
case "2": {
    $DB=DBC::get();
    switch ($_REQUEST['ACTION']) {
    case 'PLAN':
        try {
            $DB->beginTransaction();
            DBC::execute("UPDATE wo SET SCHEDSTARTDATE=:schedstartdate,DUMMY='PLAN',WOSTATUS='PL' WHERE WONUM=:wonum",array("wonum"=>$_REQUEST['ID'],"schedstartdate"=>$_REQUEST['SCHEDSTARTDATE']));
/**
* We calculate the remaining hours and reassigns them to UNASSIGNED on the new SCHEDSTARTDATE
*/
            $planned_hrs=DBC::fetchcolumn("SELECT SUM(ESTHRS) FROM wocraft WHERE WONUM='{$_REQUEST['ID']}'",0);
            $real_hrs=DBC::fetchcolumn("SELECT SUM(REGHRS) FROM woe WHERE WONUM='{$_REQUEST['ID']}'",0);
            if (is_float($real_hrs)) {
                $saldo_hrs=floatval($planned_hrs)-floatval($real_hrs);   
            } else {
                $saldo_hrs=floatval($planned_hrs);
            }
            DBC::execute("INSERT IGNORE woe (WONUM,EMPCODE,WODATE,ESTHRS) VALUES (:wonum,'UNASSIGNED',:wodate,:esthrs)",array("wonum"=>$_REQUEST['ID'],"wodate"=>$_REQUEST['SCHEDSTARTDATE'],"esthrs"=>$saldo_hrs));
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
        break;
    }
?>    
<script type="text/javascript">
function reload() {    
    window.location="<?PHP echo $_SERVER['SCRIPT_NAME']."?STEP=1&EQNUM={$_REQUEST['EQNUM']}"; ?>"; 
} 
setTimeout("reload();", 500);
</script>
<?PHP
    break;
} // EO STEP2 
case "3": {

    for ($c=0; $c < sizeof($_REQUEST['WONUM']); $c++) {
        $wo=$_REQUEST['WONUM'][$c];
        $schedstartdate=$_REQUEST['SCHEDSTARTDATE'];
        try {
            $DB->beginTransaction();
            DBC::execute("UPDATE wo SET SCHEDSTARTDATE=:schedstartdate,WOSTATUS='PL' WHERE WONUM=:wonum",array("wonum"=>$wo,"schedstartdate"=>$schedstartdate));
/**
* We calculate the remaining hours and reassigns them to UNASSIGNED on the new SCHEDSTARTDATE
*/
            $planned_hrs=DBC::fetchcolumn("SELECT SUM(ESTHRS) FROM wocraft WHERE WONUM='{$wo}'",0);
            $real_hrs=DBC::fetchcolumn("SELECT SUM(REGHRS) FROM woe WHERE WONUM='{$wo}'",0);
            if (is_float($real_hrs)) {
                $saldo_hrs=floatval($planned_hrs)-floatval($real_hrs);   
            } else {
                $saldo_hrs=floatval($planned_hrs);
            }
            DBC::execute("INSERT IGNORE woe (WONUM,EMPCODE,WODATE,ESTHRS) VALUES (:wonum,'UNASSIGNED',:wodate,:esthrs)",array("wonum"=>$wo,"wodate"=>$schedstartdate,"esthrs"=>$saldo_hrs));
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
    } 
?>    
<script type="text/javascript">
function reload() {    
    window.location="<?PHP echo $_SERVER['SCRIPT_NAME']."?STEP=1&EQNUM={$_REQUEST['EQNUM']}&1CLICK=on"; ?>"; 
} 
setTimeout("reload();", 500);
</script>
<?PHP
    break;
} 
default: {
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('lines',$DB->query("SELECT EQLINE AS 'id',EQLINE AS 'text' FROM tbl_eqline",PDO::FETCH_NUM));
    $tpl->assign('version',"$Id: weekplanning.php,v 1.1 2013/08/30 14:48:23 werner Exp $");
    $tpl->display_error("weekplanning_form.tpl");
    break;
} // EO default
} // EO Switch
?>