<?php
// Testing jpquery functionality
require("../includes/config_mycmms.inc.php");
require("setup.php");

$tpl=new smarty_mycmms();
$tpl->assign("name","Werner Huysmans");
$tpl->assign("address","Rue Saint Georges 11, 1400 Nivelles");
$tpl->display("test_jq.tpl");
?>
