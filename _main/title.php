<?PHP 
/** 
* myCMMS title
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package framework
* @subpackage title_frame
* @filesource
* CVS
* $Id: title.php,v 1.2 2013/04/17 05:34:55 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/_main/title.php,v $
* $Log: title.php,v $
* Revision 1.2  2013/04/17 05:34:55  werner
* Inserted CVS variables Id,Source and Log
*
*/
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php"); // Get Logo
require("setup.php");
$system=$_SESSION['system'];    # system can be td, production, oee
$DB=DBC::get();

$tpl=new smarty_mycmms();
$tpl->debugging=false;
$tpl->assign('stylesheet',STYLE_PATH."title.css");
$tpl->assign('logo',LOGO_TD);
$tpl->assign('tabs',$DB->query("SELECT tab,tabheader FROM sys_mainwindow WHERE system='{$system}' ORDER BY taborder",PDO::FETCH_ASSOC));
$tpl->assign('nav',$_SESSION['nav']);   // Selected Tab
$tpl->assign('user',$_SESSION['user']); // Logged in as 
$tpl->assign('DB',$_SESSION['db']);     // Active DB
switch ($system) {
    case 'td':
        $tpl->assign('index',"index.php");      // Default
        $tpl->assign('auth',"auth.php");   // Default            
        break;
    case 'production':
        $tpl->assign('index',"index_prod.php"); 
        $tpl->assign('auth',"auth.php");   
        break;            
    case 'oee':
        $tpl->assign('index',"index_".$system.".php");      
        $tpl->assign('auth',"auth.php");   
        break;
    case 'home':
        $tpl->assign('index',"index.php");      
        $tpl->assign('auth',"auth.php");   
    default:
        $tpl->assign('index',"index.php");      // Default
        $tpl->assign('auth',"auth.php");   // Default            
        break;
}
$tpl->display_error("fw/title.tpl");
?>