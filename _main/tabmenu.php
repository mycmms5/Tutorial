<?PHP
/** 
* TabMenu for notifications
* 
* @author  Werner Huysmans
* @access  public
* @package framework
* @subpackage tabmenu
* @filesource
* 
* CVS
* $Id: tabmenu.php,v 1.4 2013/08/19 13:26:36 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/system/tabmenu.php,v $
* $Log: tabmenu.php,v $
* Revision 1.4  2013/08/19 13:26:36  werner
* Handling webcal calls
*
* Revision 1.3  2013/08/07 11:07:25  werner
* Sync
*
* Revision 1.2  2013/07/29 10:12:14  werner
* Modification to work with PlanCalendar
*
* Revision 1.1  2013/06/08 11:40:12  werner
* new generic tabmenu container
* see also functions.js and libraries
*
* 
* DO NOT CHANGE THE FOLLOWING
* Parameter $tm is located in sys_queries.template_section
*/
session_start();
/** PlanCalendar calls tabmenu.php directly
* See the code in view_mycmms_plan.tpl (Line: 151)
*/
$_SESSION['tabwindow']=$_REQUEST['tm'];
switch ($_SESSION['tabwindow']) {
case "webcal":
    $wo_data=$_SESSION['wo_data'];
    if ($_REQUEST['id'] <> NULL) {
    $_SESSION['Ident_1']=$_REQUEST['id'];
    $_SESSION['Ident_2']=$_REQUEST['date'];
    $_SESSION['webcal_user']=$_REQUEST['user'];
    }
    break;
case "file":
    $_SESSION['filename']=$_REQUEST['filename'];
    break;
default:
    if (isset($_REQUEST['id1'])) {  // Save in Se
        $_SESSION['Ident_1']=$_REQUEST['id1'];
        $_SESSION['Ident_2']=$_REQUEST['id2'];
    }
    break;
}    
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php");
require("setup.php");
$DB=DBC::get();
if(!empty($_GET['nav_tab'])) {   
    $_SESSION['nav_tab']=$_GET['nav_tab']; 
} else {
    $_SESSION['nav_tab']=DBC::fetchcolumn("SELECT tablink FROM sys_tabwindows WHERE tabwindow='{$_SESSION['tabwindow']}' AND taborder=0",0);
}
$result=$DB->query("SELECT tablink,tabaction FROM sys_tabwindows WHERE tabwindow='{$_SESSION['tabwindow']}'");
foreach ($result->fetchAll(PDO::FETCH_ASSOC) AS $tab) {
    $action[$tab['tablink']]=$tab['tabaction'];   
}

$tpl=new smarty_mycmms();
$tpl->assign("nav_tab",$_SESSION['nav_tab']);
$tpl->assign("title","tabmenu_title.php");
$tpl->assign("action",$action[$_SESSION['nav_tab']]);
$tpl->display_error("fw/tw.tpl");
?>
