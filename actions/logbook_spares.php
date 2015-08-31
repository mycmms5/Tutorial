<?php
/**
* @author  Werner Huysmans 
* @access  public
* @package warehouse
* @subpackage logbook
* @filesource
* CVS
* $Id: logbook_spares.php,v 1.3 2013/05/12 08:06:00 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/logbook_spares.php,v $
* $Log: logbook_spares.php,v $
* Revision 1.3  2013/05/12 08:06:00  werner
* SPARES now contains the stock data
*
* Revision 1.2  2013/04/17 05:52:11  werner
* Inserted CVS variables Id,Source and Log
*

*/
require("../includes/config_mycmms.inc.php");
$version=__FILE__." :V5.0 Build 20150808";
require("setup.php");
require("lib_queries.php");

switch ($_REQUEST['STEP']) {
case "1": {
    $DB=DBC::get();
    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    
    if ($_REQUEST['LOOK_TEXT']=="on") {
        $tpl->assign("spares",$DB->query("SELECT i.ITEMNUM,i.DESCRIPTION,i.NOTES,s.LOCATION,s.QTYONHAND FROM invy i LEFT JOIN stock s ON i.ITEMNUM=s.ITEMNUM WHERE DESCRIPTION LIKE '%{$_REQUEST['TEXT']}%'",PDO::FETCH_ASSOC));
    }
    if ($_REQUEST['LOOK_BOM']=="on") {
        $found=DBC::fetchcolumn("SELECT COUNT(*) FROM equip WHERE EQNUM='{$_REQUEST['EQNUM']}'",0);
        if ($found==0) {
            PDO_log("WO_ERROR:EQNUM");
        } else {
            $sparecode=DBC::fetchcolumn("SELECT SPARECODE FROM equip WHERE EQNUM='{$_REQUEST['EQNUM']}'",0);
            $tpl->assign("spares",$DB->query("SELECT sp.ITEMNUM,i.DESCRIPTION,s.LOCATION,s.QTYONHAND FROM spares sp LEFT JOIN invy i ON sp.ITEMNUM=i.ITEMNUM LEFT JOIN stock s ON i.ITEMNUM=s.ITEMNUM WHERE sp.SPARECODE='$sparecode'",PDO::FETCH_ASSOC));
        }
    }
    if ($_REQUEST['LOOK_SAP']=="on") {
        $tpl->assign("spares",$DB->query("SELECT i.ITEMNUM,i.DESCRIPTION,i.NOTES,s.LOCATION,s.QTYONHAND FROM invy i LEFT JOIN stock s ON i.ITEMNUM=s.ITEMNUM WHERE i.SAP LIKE '{$_REQUEST['SAP']}'",PDO::FETCH_ASSOC));
        // WOP
        if ($_REQUEST['WOP']=="on") {
            $tpl->assign("with_wo",true);
            $tpl->assign("wos",$DB->query("SELECT wop.ITEMNUM,wo.WONUM,wo.EQNUM,wo.TASKDESC,wo.REQUESTDATE,wop.QTYREQD,wop.QTYUSED FROM wop LEFT JOIN wo ON wop.WONUM=wo.WONUM WHERE wop.ITEMNUM='{$_REQUEST['SAP']}' ORDER BY wo.WONUM DESC LIMIT 0,100",PDO::FETCH_ASSOC));
        }
    } 
    $tpl=new smarty_mycmms();
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->version=$version; 
    $tpl->display_error("logs/logbook_spares_list.tpl");
    unset($_SESSION['PDO_ERROR']);
    break;
} // EO STEP1
default: {
    $DB=DBC::get();
    require("_wikihelp.php");
    
    $tpl=new smarty_mycmms();
    $tpl->version=$version; 
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->display_error("logs/logbook_spares_form.tpl");
    break;
} // EO default
} // EO Switch
?>
