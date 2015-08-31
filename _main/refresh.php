<?php
session_start();
// DebugBreak();
require_once '../includes/functions_mycmms.php';   # DB connection
$DB=DBC::get();
$result=$DB->query("SELECT WONUM,EMPCODE,ESTHRS FROM woe WHERE ID={$_REQUEST['id']}");
$woe=$result->fetch(PDO::FETCH_ASSOC);
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
      
require("setup.php");
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->debugging=false;
$tpl->display_error("refresh.tpl");
?>
<script type="text/javascript">
    setTimeout("refreshParent();",250);
</script>
