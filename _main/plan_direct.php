<?php
/** 
* @author Werner Huysmans
* @package webcalendar
* @subpackage views
* @filesource
* CVS
* $Id: plan_direct.php,v 1.2 2013/08/19 13:35:45 werner Exp $
* $Source: /var/www/cvs/mycmms_wc/myCMMS_WC/_main/plan_direct.php,v $
* $Log: plan_direct.php,v $
* Revision 1.2  2013/08/19 13:35:45  werner
* Handling ADD, MOVE, DELETE planning data - no hours are changed
*
* Revision 1.1  2013/07/29 10:33:15  werner
* PlanCalendar planning on-screen (no interaction possible)
*
**/
session_start();
$_SESSION['wo_data']=$_REQUEST;
$_SESSION['plan_data']=$_COOKIE;
require_once '../includes/functions_mycmms.php';   # DB connection
$DB=DBC::get();
$result=$DB->query("SELECT WONUM,EMPCODE,ESTHRS FROM woe WHERE ID={$_COOKIE['WO']}");
$woe=$result->fetch(PDO::FETCH_ASSOC);
switch ($_REQUEST['ACTION']) {
case 'PLAN': {
    try {
        $DB->BeginTransaction();
        DBC::execute("INSERT INTO woe (WONUM,EMPCODE,OPNUM,WODATE,ESTHRS) VALUE (:wonum,:empcode,10,:wodate,:esthrs)",
            array(
            "wonum"=>$woe['WONUM'],
            "wodate"=>$_COOKIE['DAY'],
            "empcode"=>$_COOKIE['TECH'],
            "esthrs"=>$woe['ESTHRS']));
        $DB->commit();            
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    }
    $_SESSION['WEBCAL_LOCATION']="pop".DBC::fetchcolumn("SELECT LAST_INSERT_ID()");    
    break;    
}
case 'MOVE': {
    try {
        $DB->beginTransaction();
        DBC::execute("DELETE FROM woe WHERE ID={$_COOKIE['WO']} AND REGHRS=0",array());        
        DBC::execute("INSERT INTO woe (WONUM,EMPCODE,OPNUM,WODATE,ESTHRS) VALUE (:wonum,:empcode,10,:wodate,:esthrs)",
            array(
            "wonum"=>$woe['WONUM'],
            "wodate"=>$_COOKIE['DAY'],
            "empcode"=>$_COOKIE['TECH'],
            "esthrs"=>$woe['ESTHRS']));
        $DB->commit();            
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    }
    $_SESSION['WEBCAL_LOCATION']="pop".DBC::fetchcolumn("SELECT LAST_INSERT_ID()");    
    break;    
}
case "DELETE": {
    try {
        $DB->beginTransaction();
        DBC::execute("DELETE FROM woe WHERE ID={$_COOKIE['WO']} AND REGHRS=0",array());        
        $DB->commit();
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    }
    break;
} // EO case DELETE
} // EO switch
?>
<script type="text/javascript">
function reload() {    
    window.location.assign("../index.php?date={$cookies.DAY}"); 
} 
setTimeout("reload();", 25);
</script>

