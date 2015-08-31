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
* $Id: logbook_ciestate.php,v 1.1 2013/12/25 08:50:01 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/logbook_ciestate.php,v $
* $Log: logbook_ciestate.php,v $
* Revision 1.1  2013/12/25 08:50:01  werner
* Synced versions with myCMMS
*
* Revision 1.4  2013/09/07 16:24:41  werner
* BUG in WOSTATUS R Line 133
*
* Revision 1.3  2013/06/08 11:28:24  werner
* Added daily status
*
* Revision 1.2  2013/04/17 05:52:11  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");
require("setup.php");
require("lib_queries.php");

/**
* SQL data into an array
*  
* @param mixed $data (result of a query)
* In our example: 200709 A 56 / 200709 R 21
* @return array $a_data
* In our example:
* array 200709(
*   "YM"=>"200709",
*   "R"=>21,
*   "A"=>56
* )
*/
function sql2array($sql_data) {
    $a_data=array();
    foreach($sql_data as $line) {
        $a_data[$line['YearMonth']]['YM']=$line['YearMonth'];
        $a_data[$line['YearMonth']][$line['DBFLD_WOSTATUS']]=$line['#'];
    }
    return $a_data; 
}

switch ($_REQUEST['STEP']) {
case "1": {
/**
* Get WOSTATUS of WO in a period (based on the REQUESTDATE)
*/    
    $DB=DBC::get();
    // Building Query
    $SELECT="SELECT PERIODE_M(REQUESTDATE) AS 'YearMonth',WOSTATUS AS 'DBFLD_WOSTATUS',COUNT(*) AS '#' FROM wo ";
    $GROUPBY=" GROUP BY YearMonth,WOSTATUS";
    if ($_REQUEST['LOOK_EVALUATION']=="on") {
        $LOOKUP1=" WHERE wo.REQUESTDATE BETWEEN '{$_REQUEST['START']}' AND '{$_REQUEST['UNTIL']}' ";
    }
    if ($_REQUEST['LOOK_INCLUDE_F']=="on") {
        $LOOKUP2=" AND WOSTATUS NOT IN ('F','C')";
    }
    $SQL=$SELECT.$LOOKUP1.$LOOKUP2.$GROUPBY;
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
    set_sql("U_LOGBOOK_WOSTATE",$SQL);
    
    if ($_REQUEST['STATIC']=="on") {
        $DB=DBC::get();
        $sql=get_sql("U_LOGBOOK_WOSTATE");
        $result=$DB->query($sql);
        $data=$result->fetchAll(PDO::FETCH_ASSOC);
        $a_wostate=sql2array($data);
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("data",$a_wostate);
        $tpl->display_error("logbook_wostate_list.tpl");
        break;
    } else {
?>        
<script type=text/javascript>
function reload()
{    window.location = "../_main/list.php?query_name=U_LOGBOOK_WOSTATE";
} 
setTimeout("reload();", 500)
</script>
<?PHP
        break;
    } // EO else
}
case "2": {
/**
* Get WOSTATUS of WO in a period (based on the REQUESTDATE)
*/    
    $DB=DBC::get();
    // Building Query
    $SELECT="SELECT WONUM AS 'DBFLD_WONUM',PRIORITY AS 'DBFLD_PRIORITY',WOSTATUS AS 'DBFLD_WOSTATUS',
        DATEDIFF(APPROVEDDATE,REQUESTDATE) AS 'DELAY_APPROVAL',
        DATEDIFF(PREPARED,APPROVEDDATE) AS 'DELAY_PREPARATION',
        DATEDIFF(SCHEDSTARTDATE,PREPARED) AS 'DELAY_PLANNING',
        DATEDIFF(COMPLETIONDATE,SCHEDSTARTDATE) AS 'DELAY_EXECUTION',
        TASKDESC AS 'DBFLD_TASKDESC' FROM wo ";
    // $GROUPBY=" GROUP BY YearMonth,WOSTATUS";
    if ($_REQUEST['LOOK_EVALUATION']=="on") {
        $LOOKUP1=" WHERE wo.REQUESTDATE BETWEEN '{$_REQUEST['START2']}' AND '{$_REQUEST['UNTIL2']}' ";
    }
    $ORDERBY=" ORDER BY PRIORITY,REQUESTDATE";
    $SQL=$SELECT.$LOOKUP1.$ORDERBY;
/**
* If we check 'static', the data will be presented in a template, 
* otherwise the list will be saved as a QUERY in sys_queries
*/
    set_sql("U_LOGBOOK_WOFLOW",$SQL);
    
    if ($_REQUEST['STATIC']=="on") {
        break;
    } else {
?>        
<script type=text/javascript>
function reload()
{    window.location = "../_main/list.php?query_name=U_LOGBOOK_WOFLOW";
} 
setTimeout("reload();", 500)
</script>
<?PHP
    } // EO else
}
default: {
    $DB=DBC::get();
    $year=$_REQUEST['YEAR'];
    $ciestatus=array();
# Sales
    $ciestatus['Sales_STORK']=DBC::fetchcolumn("SELECT SUM(REVENUES) FROM cash WHERE  QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '700210001'",0);
    $ciestatus['Sales_DEMB']=DBC::fetchcolumn("SELECT SUM(REVENUES) FROM cash WHERE  QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '700210002'",0);
    $ciestatus['Sales_REMACLE']=DBC::fetchcolumn("SELECT SUM(REVENUES) FROM cash WHERE  QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '700210003'",0);
    $ciestatus['Sales_DESTROOPER']=DBC::fetchcolumn("SELECT SUM(REVENUES) FROM cash WHERE  QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '700210004'",0);    
    $ciestatus['SALES']=DBC::fetchcolumn("SELECT SUM(REVENUES) FROM cash WHERE  QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '700210%'",0);    
# Generic Costs    
    $ciestatus['GC_FORFAIT']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '610%'",0);
    $ciestatus['GC_Office']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612030'",0);
    $ciestatus['GC_IT']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612031'",0);
    $ciestatus['GC_Publicity']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612032'",0);
    $ciestatus['GC_Documentation']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612041'",0);
    $ciestatus['ADIC']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612060'",0);
    $ciestatus['AUTO_FUEL']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612081'",0);
    $ciestatus['AUTO_MAINT']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612082'",0);
    $ciestatus['AUTO_INS']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '612083'",0);
    $ciestatus['AUTO']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '61208%'",0);
# Social    
    $ciestatus['HDP']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER='613060'",0);
# Insurances
    $ciestatus['Insurances']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '614040'",0);    
# Pro Costs
    $ciestatus['PC_WEB']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '615001'",0);
    $ciestatus['PC_SW']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '615030'",0);
    $ciestatus['INSPRO']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '615040'",0);
    $ciestatus['EPI']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '615050'",0);

    $ciestatus['COSTS']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER LIKE '61%'",0);
    $ciestatus['Investments']=DBC::fetchcolumn("SELECT SUM(EXPENSES) FROM cash WHERE QUARTER LIKE '{$year}%' AND GENLEDGER='1400'",0);
    $ciestatus['AMORTISATION']=DBC::fetchcolumn("SELECT SUM(Y".$year.") FROM amortisation",0);
    $ciestatus['RESULT']=$ciestatus['SALES']-$ciestatus['COSTS']-$ciestatus['AMORTISATION'];
    require("_wikihelp.php");
    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
#    $tpl->assign("wiki",get_wiki_help($_SERVER['SCRIPT_NAME']));
    $tpl->assign("YEAR",$year);
    $tpl->assign("ciestatus",$ciestatus);
    $tpl->assign("from",now2string(false));
    $tpl->assign("until",now2string(false));
    $tpl->display_error("logs/logbook_ciestate_form.tpl");
    break;
} // EO default
}
?>
