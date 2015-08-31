<?php
/**
* Utility to show GANTT, with filter
* 
* @author  Werner Huysmans 
* @access  public
* @package JPGRAPH
* @subpackage jpgraph
* @filesource
*/
require("../includes/config_mycmms.inc.php");
require("HTML/Table.php");
require(CMMS_LIB."/class_actionPage.php");

switch ($_REQUEST['STEP']) {
case "1": { 
    require(CMMS_LIB."/class_jpgraph_gantt.php");
    $DB=DBC::get();
    $gantt=new JPG_GANTT();
    $gantt->gantt_title="myCMMS planning";
    $gantt->init("DWM");
    // Adding activities
    $result=$DB->query("SELECT CONCAT(WONUM,':',LEFT(TASKDESC,20)) AS 'WO',SCHEDSTARTDATE AS 'START',ADDDATE(SCHEDSTARTDATE,1) AS 'END',EQNUM AS 'RESS' FROM wo WHERE WOSTATUS IN ('PL') AND EQNUM LIKE '{$_REQUEST['EQNUM']}%' AND SCHEDSTARTDATE BETWEEN '{$_REQUEST['FROM']}' AND '{$_REQUEST['UNTIL']}'");
if ($result) {
    $wo=array();
    $i=0;
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $wo) {
        $gantt->add_activity($i,$wo["WO"],$wo["START"],$wo["END"],$wo["RESS"]);
        $i++;   // Increment
    }
}
$gantt->show_GANTT();

    break;
}
default: {
    $action=new actionPage();
    $action->stylesheet=CSS_INPUT;
    $action->calendar=true;
    $action->wiki="";
    $action->HeaderPage(_("GANTT"));
?>
<table>
<tr><td>
<form action="<?PHP echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="STEP" value="1">
    <?PHP echo create_date_box("FROM","FROM",10,""); ?></td><td><?PHP echo create_date_box("UNTIL","UNTIL",10,""); ?></td></tr>
<tr><td colspan="2"><?PHP echo create_combo("SELECT EQLINE AS 'id',EQLINE AS 'text' FROM tbl_EQLINE","EQNUM","",""); ?></td></tr>
<tr><td colspan="2"><input type="submit" name="check" value="<?PHP echo _("Pre-Select Equipment"); ?>"></td></tr>
</form>
</table>
<?PHP    
    $action->FooterPage();
    break;   
} // EO switch
}    
?>
