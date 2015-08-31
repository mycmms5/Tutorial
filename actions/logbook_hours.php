<?php
/**
* Logbook for reported hours
* 
* @author  Werner Huysmans 
* @access  public
* @package work
* @subpackage logbook
* @filesource
* CVS
* $Id: logbook_hours.php,v 1.3 2013/06/08 11:28:00 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/logbook_hours.php,v $
* $Log: logbook_hours.php,v $
* Revision 1.3  2013/06/08 11:28:00  werner
* SELECT query modified
*
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
* Get all woe.REGHRS - criteria : 
* - woe.EMPCODE
* - woe.WODATE
* - wo.EQNUM
* 
* The WHERE 1 allows adding other criteria
*/
    $SELECT="SELECT woe.WONUM AS 'DBFLD_WONUM',wo.EQNUM AS 'DBFLD_EQNUM',wo.TASKDESC AS 'DBFLD_TASKDESC',wo.REQUESTDATE AS 'DBFLD_REQUESTDATE',woe.EMPCODE AS 'DBFLD_EMPCODE',woe.WODATE AS 'DBFLD_WODATE', woe.REGHRS AS 'DBFLD_REGHRS' FROM woe LEFT JOIN wo ON woe.WONUM=wo.WONUM WHERE";
    $LOOKUP= " 1 ";    
    $ORDERBY=" ORDER BY woe.WONUM DESC LIMIT 0,100";    
    if ($_REQUEST['LOOK_ASSIGNEDTECH']=="on") {
        $LOOKUP.=" AND woe.EMPCODE='{$_REQUEST['ASSIGNEDTECH']}'";
    }
    if ($_REQUEST['LOOK_WORKPERIOD']=="on") {
        $LOOKUP.=" AND woe.WODATE BETWEEN '{$_REQUEST['DT1']}' AND '{$_REQUEST['DT2']}'";
    }
    if ($_REQUEST['LOOK_EQNUM']=="on") {
        $LOOKUP.=" AND wo.EQNUM LIKE'{$_REQUEST['EQNUM']}%'";
    } 
    $DB=DBC::get();
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
if ($_REQUEST['STATIC']=="on") {
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("woes",$DB->query($SELECT.$LOOKUP.$ORDERBY,PDO::FETCH_ASSOC));
    $tpl->display_error("logbook_hours_list.tpl");
} else {
     set_sql("U_HOURS_LBHOURS",$SELECT.$LOOKUP.$ORDERBY);
?>        
<script type=text/javascript>
function reload() {    
    window.location = "../_main/list.php?query_name=<?PHP echo "U_HOURS_LBHOURS"; ?>";
} 
setTimeout("reload();", 500)
</script>
<?PHP
    } // EO reload dynamic list
    break;
} // EO STEP1
default: {
    $DB=DBC::get();
    require("_wikihelp.php");
   
    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('fromdate',strtotime('-1 month'));
    $tpl->assign("assignedtechs",$DB->query("SELECT uname,longname FROM sys_groups WHERE profile & 64 <> 0",PDO::FETCH_NUM));
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->display_error("logs/logbook_hours_form.tpl");
    break;
} // EO default
} // EO Switch
?>
