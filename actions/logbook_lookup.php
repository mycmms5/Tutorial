<?php
/** 
* Logbook lookup with several search criteria
* 
* @author  Werner Huysmans
* @access  public
* @package work
* @subpackage logbook
* @filesource
* CVS
* $Id: logbook_lookup.php,v 1.5 2013/05/12 08:04:24 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/logbook_lookup.php,v $
* $Log: logbook_lookup.php,v $
* Revision 1.5  2013/05/12 08:04:24  werner
* Check on valid EQNUM
* Reorganisation of SELECT statements
* unset PDO_ERROR
*
* Revision 1.4  2013/04/27 13:23:49  werner
* no message
*
*/
require("../includes/config_mycmms.inc.php");
$version=__FILE__." :V5.0 Build 20150808";
require("setup.php");
require("lib_queries.php");

switch ($_REQUEST['STEP']) {
case "1": {
/**
* Get all WO with criteria
* When group1 is checked, REQUESTDATE, SCHEDSTARTDATE and COMPLETIONDATE are evaluated
* When group2 is checked only wo_assign.ENDED is evaluated
*/   
    DBC::execute("UPDATE wo SET TEXTS_A='Method' WHERE WOSTATUS NOT IN ('C') AND TEXTS_A IS NULL",array());
    DBC::execute("UPDATE wo SET TEXTS_B='Feedback' WHERE WOSTATUS NOT IN ('C') AND TEXTS_B IS NULL",array());
    if (empty($_SESSION['QBE']['EQNUM'])) {   $_SESSION['QBE']['EQNUM']=$_SESSION['LINE']; }
/**
* Normal SELECT statement
*/
    $SELECT="SELECT WONUM AS 'DBFLD_WONUM',wo.EQNUM AS 'DBFLD_EQNUM',equip.DESCRIPTION AS 'EQUIP_DESC',ORIGINATOR AS 'DBFLD_ORIGINATOR',REQUESTDATE AS 'DBFLD_REQUESTDATE',DATE_FORMAT(SCHEDSTARTDATE,'%Y-%m-%d') AS 'DBFLD_SCHEDSTARTDATE',DATE_FORMAT(COMPLETIONDATE,'%Y-%m-%d') AS 'DBFLD_COMPLETIONDATE',TASKDESC AS 'DBFLD_TASKDESC',TEXTS_B AS 'DBFLD_TEXTS_B',RFFCODE AS 'DBFLD_RFFCODE',TEXTS_PPM AS 'TEXTS_PPM',PRIORITY AS 'DBFLD_PRIORITY',WOSTATUS AS 'DBFLD_WOSTATUS' FROM wo LEFT JOIN equip ON wo.EQNUM=equip.EQNUM WHERE ";
    $LOOKUP= " 1 ";    
    if ($_REQUEST["group"]=="group1") {
        if ($_REQUEST['LOOK_DT1_2']=="on") {
            $_SESSION['QBE']['DT1']=$_REQUEST['DT1'];
            $_SESSION['QBE']['DT2']=$_REQUEST['DT2'];
            $LOOKUP.=" AND REQUESTDATE BETWEEN '{$_REQUEST['DT1']}' AND '{$_REQUEST['DT2']}' ";
        }   // REQUESTDATE 
        if ($_REQUEST['LOOK_DT3_4']=="on") {
            $_SESSION['QBE']['DT3']=$_REQUEST['DT3'];
            $_SESSION['QBE']['DT4']=$_REQUEST['DT4'];
            $LOOKUP.=" AND SCHEDSTARTDATE BETWEEN '{$_REQUEST['DT3']}' AND '{$_REQUEST['DT4']}' ";
        }   // SCHEDSTARTDATE 
        if ($_REQUEST['LOOK_DT5_6']=="on") {
            $_SESSION['QBE']['DT5']=$_REQUEST['DT5'];
            $_SESSION['QBE']['DT6']=$_REQUEST['DT6'];
            $LOOKUP.=" AND COMPLETIONDATE BETWEEN '{$_REQUEST['DT5']}' AND '{$_REQUEST['DT6']}' ";
        }   // COMPLETIONDATE 
    } // EO group
    if ($_REQUEST['LOOK_TEXT']=="on") {
        $_SESSION['QBE']['TEXT']=$_REQUEST['TEXT'];
        $LOOKUP.=" AND TASKDESC like '%{$_REQUEST['TEXT']}%'";
    }
    if ($_REQUEST['LOOK_EQNUM']=="on") {
        $_SESSION['QBE']['EQNUM']=$_REQUEST['EQNUM'];
        $LOOKUP.=" AND wo.EQNUM LIKE '{$_REQUEST['EQNUM']}%'";
        $found=DBC::fetchcolumn("SELECT COUNT(*) FROM equip WHERE EQNUM='{$_REQUEST['EQNUM']}'",0);
        if ($found==0) {
            PDO_log("WO_ERROR:EQNUM");
        } 
    }
/** 
* EQLINE is not the standard   
    if ($_REQUEST['LOOK_EQLINE']=="on" AND $_REQUEST['LOOK_EQNUM']!="on") {
        $_SESSION['QBE']['EQNUM']=$_REQUEST['EQNUM'];
        $LOOKUP.=" AND equip.EQROOT = '{$_REQUEST['EQNUM']}'";
    }
*/
    if ($_REQUEST['LOOK_ORIGINATOR']=="on" AND count($_REQUEST['ORIGINATOR']) >0 ) {
        $LOOKUP.=" AND ORIGINATOR IN ('x'";
        foreach ($_REQUEST['ORIGINATOR'] as $origin) {
            $LOOKUP.=",'$origin'";
        }
        $LOOKUP.=")";
    }
    if ($_REQUEST['LOOK_TECHNICIAN']=="on" AND count($_REQUEST['ASSIGNEDTECH']) >0 ) {
        $LOOKUP.=" AND ASSIGNEDTECH IN ('x'";
        foreach ($_REQUEST['ASSIGNEDTECH'] as $tech) {
            $LOOKUP.=",'$tech'";
        }
        $LOOKUP.=")";
    }
    if ($_REQUEST['LOOK_PRIORITY']=="on" AND count($_REQUEST['PRIORITY']) > 0) {
        $LOOKUP.=" AND PRIORITY IN (-1";
        foreach ($_REQUEST['PRIORITY'] as $prio) {
            $LOOKUP.=",$prio";
        }
        $LOOKUP.=")";
    }
    if ($_REQUEST['LOOK_WOSTATUS']=="on" AND count($_REQUEST['WOSTATUS']) > 0) {
        $LOOKUP.=" AND WOSTATUS IN ('x'";
        foreach ($_REQUEST['WOSTATUS'] as $status) {
            $LOOKUP.=",'$status'";
        }
        $LOOKUP.=")";
    }
    if ($_REQUEST['group']=="group2") {
        if ($_REQUEST['LOOK_DT7_8']=="on" ) {
            $_SESSION['QBE']['DT7']=$_REQUEST['DT7'];
            $_SESSION['QBE']['DT8']=$_REQUEST['DT8'];
            $SELECT="SELECT WONUM AS 'DBFLD_WONUM',wo.EQNUM AS 'DBFLD_EQNUM',ORIGINATOR AS 'DBFLD_ORIGINATOR',DATE_FORMAT(REQUESTDATE,'%Y-%m-%d') AS 'DBFLD_REQUESTDATE',DATE_FORMAT(SCHEDSTARTDATE,'%Y-%m-%d') AS 'DBFLD_SCHEDSTARTDATE',DATE_FORMAT(COMPLETIONDATE,'%Y-%m-%d') AS 'DBFLD_COMPLETIONDATE',TASKDESC AS 'DBFLD_TASKDESC',TEXTS_B AS 'DBFLD_TEXTS_B',PRIORITY AS 'DBFLD_PRIORITY',WOSTATUS AS 'DBFLD_WOSTATUS' FROM wo LEFT JOIN equip ON wo.EQNUM=equip.EQNUM";
            $LOOKUP=" WHERE wo.EQNUM LIKE '{$_REQUEST['EQNUM']}%' AND WONUM IN (SELECT WONUM FROM wo_assign wa WHERE wa.ENDED BETWEEN '{$_REQUEST['DT7']}' AND '{$_REQUEST['DT8']}' AND wa.ASSIGNEDTECH LIKE '{$_REQUEST['ASSIGNEDTECH'][0]}%')";
        } // EO Look_DT7_8
    } // EO group2

/**
* Compose QUERY: SELECT + LOOKUP's + ORDER BY statement
*/
    $ORDERBY=" ORDER BY WONUM DESC LIMIT 0,100";    
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
    set_sql("U_".$_SESSION['user']."_LBLU",$SELECT.$LOOKUP.$ORDERBY);

    if ($_REQUEST['STATIC']=="on") {
        $DB=DBC::get();
        $sql=get_sql("U_".$_SESSION['user']."_LBLU");
        $result=$DB->query($sql);
        $data=$result->fetchAll(PDO::FETCH_ASSOC);
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->version=$version; 
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("data",$data);
        $tpl->display_error("logs/wo_lookup_list.tpl");
        unset($_SESSION['PDO_ERROR']);
    } else {
?>        
<script type=text/javascript>
function reload() {    
    window.location = "../_main/list.php?query_name=<?PHP echo "U_".$_SESSION['user']."_LBLU"; ?>";
} 
setTimeout("reload();", 500)
</script>
<?PHP
    } // EO reload dynamic list
    break;
} // EO STEP1
default: {
    unset($_SESSION['PDO_ERROR']);
    $DB=DBC::get();
    require("_wikihelp.php");
    
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->version=$version; 
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('stylesheet_calendar',STYLE_PATH."/".CSS_CALENDAR);
    $tpl->assign("data",$_SESSION);
    $tpl->assign("assignedtechs",$DB->query("SELECT uname,longname FROM sys_groups WHERE profile & 64 <> 0",PDO::FETCH_NUM));
    $tpl->assign("originators",$DB->query("SELECT uname,longname FROM sys_groups",PDO::FETCH_NUM));
    $tpl->assign("wostati",$DB->query("SELECT WOSTATUS,DESCRIPTION FROM wostatus",PDO::FETCH_NUM));
    $tpl->assign("priorities",$DB->query("SELECT PRIORITY,DESCRIPTION FROM wopriority",PDO::FETCH_NUM));
    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->display_error("logs/wo_lookup_form.tpl");
    break;

} // EO default
} // EO Switch
?>
