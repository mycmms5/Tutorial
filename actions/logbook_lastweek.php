<?php
/** 
* Logbook for Shift-transfer
* 
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage logbook
* @filesource
*/
require("../includes/config_mycmms.inc.php");
$version=__FILE__." :V5.0 Build 20150808";
require("setup.php");
require("lib_queries.php");

switch ($_REQUEST['STEP']) {
case "1": {
/**
* Get all WO : 
* - Finished in the PERIOD
* - Commented in the PERIOD
* - wo.EQNUM
*/    
    $DB=DBC::get();
    // Building Query
    $SELECT="(SELECT wo.WONUM AS 'DBFLD_WONUM',wo.EQNUM AS 'DBFLD_EQNUM',equip.DESCRIPTION AS 'EQUIP_DESC',ORIGINATOR AS 'DBFLD_ORIGINATOR',DATE_FORMAT(REQUESTDATE,'%Y-%m-%d')  AS 'DBFLD_REQUESTDATE',TASKDESC AS 'DBFLD_TASKDESC',TEXTS_B AS 'DBFLD_TEXTS_B',RFFCODE AS 'DBFLD_RFF',TEXTS_PPM AS 'TEXTS_PPM',DATE_FORMAT(SCHEDSTARTDATE,'%Y-%m-%d') AS 'DBFLD_SCHEDSTARTDATE',DATE_FORMAT(COMPLETIONDATE,'%Y-%m-%d') AS 'DBFLD_COMPLETIONDATE',PRIORITY AS 'DBFLD_PRIORITY',WOSTATUS AS 'DBFLD_WOSTATUS' FROM wo LEFT JOIN equip ON wo.EQNUM=equip.EQNUM  LEFT JOIN wo_assign ON wo.WONUM=wo_assign.WONUM";
    $LOOKUP1=" WHERE wo.EQNUM LIKE '{$_REQUEST['machine']}%' AND WOSTATUS='F' AND (COMPLETIONDATE > DATE_ADD(NOW(),INTERVAL -{$_REQUEST['preview']} DAY)))";
//    $LOOKUP2=" WHERE wo.EQNUM LIKE '{$_REQUEST['machine']}%' AND WOSTATUS='PR' AND (wo_assign.ENDED > DATE_ADD(NOW(),INTERVAL -{$_REQUEST['preview']} DAY)))";
    $LOOKUP3=" WHERE wo.EQNUM LIKE '{$_REQUEST['machine']}%' AND WOSTATUS='PR' AND (wo.REQUESTDATE > DATE_ADD(NOW(),INTERVAL -{$_REQUEST['preview']} DAY)))";
    $UNION_SQL=$SELECT.$LOOKUP3." UNION ".$SELECT.$LOOKUP1;
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
    set_sql("U_LOGBOOK_LASTWEEK",$UNION_SQL);
    if ($_REQUEST['STATIC']=="on") {
        $sql=get_sql("U_LOGBOOK_LASTWEEK");
        $result=$DB->query($sql);
        $data=$result->fetchAll(PDO::FETCH_ASSOC);
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->version=$version; 
        $tpl->assign("step","LIST");
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("data",$data);
        $tpl->display_error("logs/logbook_lastweek.tpl");
    } else {
?>        
<script type=text/javascript>
function reload()
{    window.location = "../_main/list.php?query_name=U_LOGBOOK_LASTWEEK";
} 
setTimeout("reload();", 500)
</script>
<?PHP
    }
    break;
}
default: {
    $DB=DBC::get();
    require("_wikihelp.php");

    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->version=$version; 
    $tpl->assign("step","FORM");
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->assign("machines",$DB->query("SELECT EQLINE AS 'id',EQLINE AS 'text' FROM tbl_eqline",PDO::FETCH_NUM));
    $tpl->display_error("logs/logbook_lastweek.tpl");
    break;
} // EO default
}
?>
