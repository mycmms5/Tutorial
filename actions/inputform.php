<?php
require("../includes/config_mycmms.inc.php");
require("setup.php"); 
$ini_array = parse_ini_file("inputform.ini", true);   
$criteria1=$ini_array[$_REQUEST['ACTION']]["criteria1"];
$criteria2=$ini_array[$_REQUEST['ACTION']]["criteria2"];
$criteria3=$ini_array[$_REQUEST['ACTION']]["criteria3"];
$buttonText=$ini_array[$_REQUEST['ACTION']]["buttonText"];
$actionScript=$ini_array[$_REQUEST['ACTION']]['actionScript'];

$tpl=new smarty_mycmms();
$tpl->debugging=false;
$tpl->caching=false;
$tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
$tpl->assign('criteria1',$criteria1);
$tpl->assign('criteria2',$criteria2);
$tpl->assign('criteria3',$criteria3);
$tpl->assign('buttonText',$buttonText);
$tpl->assign('actionScript',$actionScript);
$tpl->display_error("fw/inputform.tpl");
?>
