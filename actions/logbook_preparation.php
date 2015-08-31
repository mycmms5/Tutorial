<?php
/** 
* Logbook for Shift-transfer
* 
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage logbook
* @filesource
* CVS
* $Id: logbook_preparation.php,v 1.2 2013/04/17 05:44:53 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/logbook_preparation.php,v $
* $Log: logbook_preparation.php,v $
* Revision 1.2  2013/04/17 05:44:53  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");
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
    $SELECT="SELECT WONUM AS 'DBFLD_WONUM',EQNUM AS 'DBFLD_EQNUM',TASKDESC AS 'DBFLD_TASKDESC', ORIGINATOR AS 'DBFLD_ORIGINATOR' , ASSIGNEDBY AS 'DBFLD_ASSIGNEDBY',ASSIGNEDTO AS 'DBFLD_ASSIGNEDTO', WOTYPE AS 'DBFLD_WOTYPE',PRIORITY AS 'DBFLD_PRIORITY',WOSTATUS AS 'DBFLD_WOSTATUS',SCHEDSTARTDATE AS 'DBFLD_SCHEDSTARTDATE' FROM wo WHERE WOSTATUS IN ('A','M','IP') AND ";
    $LOOKUP1=" ASSIGNEDBY='{$_REQUEST['ASSIGNEDBY']}'";
    $SQL=$SELECT.$LOOKUP1;
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
    set_sql("U_LOGBOOK_PREPARATION",$SQL);
?>        
<script type=text/javascript>
function reload()
{    window.location = "../_main/list.php?query_name=U_LOGBOOK_PREPARATION";
} 
setTimeout("reload();", 500)
</script>
<?PHP
}
default: {
    $DB=DBC::get();
    require("_wikihelp.php");
    
    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->assign("department",$_SESSION['dept']);
    $approvers=array("VERBELENN","MOENSB","CROLLETB",
        "BATYRA","KENISL","VANDENEYNDEB",
        "HUYSMANS","VANKEERP","BALB",
        "FIERENSG","IMBRECHTSG","DUERINCKXD",
        "GODDEM","LUYTENSK","DEROOSE");
    $tpl->assign("approvers",$approvers);
    // $tpl->assign("approvers",$DB->query("SELECT uname,longname FROM sys_groups",PDO::FETCH_NUM));
    $tpl->display_error("logbook_preparation_form.tpl");
    break;
} // EO default
}
?>
