<?PHP 
/** 
* TabMenu for notifications
* 
* @author  Werner Huysmans
* @access  public
* @package framework
* @subpackage tabmenu
* @filesource
*/
/** Parameters
* $tabwindow
* $defaultaction
*/
session_start();
$tabwindow=$_SESSION['tabwindow'];
/** DO NOT CHANGE THE FOLLOWING
*/
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php");
require("setup.php");
$DB=DBC::get();

$tpl=new smarty_mycmms();
$tpl->debugging=false;
$tpl->assign('stylesheet',STYLE_PATH."title.css");
$tpl->assign("index","tabmenu.php?tm={$tabwindow}");
$tpl->assign("settings",$_SESSION);
$tpl->assign("tabs",$DB->query("SELECT tablink,tabheader FROM sys_tabwindows WHERE tabwindow='$tabwindow' ORDER BY taborder",PDO::FETCH_ASSOC));
$tpl->display_error("fw/twt.tpl");
?>