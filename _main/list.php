<?PHP 
/** 
* The list.php manages the content of the Maintmain window
* LIST retrieves the SQL query and then manipulates the query:
* - replaces the parameters
* - shows the fields with an alias
* - handles LIMIT and ORDER
*
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package framework
* @subpackage list_frame
* @filesource
* @todo Move Login Screen to Smarty Template 
* CVS
* $Id: list.php,v 1.3 2013/06/08 11:26:45 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/_main/list.php,v $
* $Log: list.php,v $
* Revision 5.0  2015/07/26  17:00:00  werner
* - Inside the DisplayLogin it's now possible to change the database
* - although there are switch statements on DisplayLogin and DisplayPageSmarty 
*/
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php");
require("class_listPageSmarty.php");  
class mainlistPage extends ListPageSmarty {
    public function DisplayLogin() {
        require("setup.php");
        $DB=DBC::get();
        $tpl=new smarty_mycmms();
        $tpl->assign('stylesheet',"../styles/lists.css");
        $tpl->assign('stylesheet_exception',"../styles/exc_login.css");
        $tpl->assign('authorisation_script',"../_main/auth.php");
        $tpl->assign('change_DB_script',"../_main/switch_DB.php");
        $tpl->display_error("fw/login.tpl");
    }
/** Alternatives    
    public function DisplayLoginList() {
        require("setup.php");
        $DB=DBC::get();
        
        $tpl=new smarty_mycmms();
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_LISTS);
        $tpl->assign('stylesheet_exception',STYLE_PATH."/".CSS_LOGIN);
        $tpl->assign('authorisation_script',"../_main/auth.php");
        $tpl->assign('names',$DB->query("SELECT uname,longname FROM sys_groups",PDO::FETCH_NUM));
        $tpl->display_error("framework_productionlogin.tpl");
    }
*/
/**    
    public function DisplayOEELogin() {
        require("setup.php");
        $DB=DBC::get();
        
        $tpl=new smarty_mycmms();
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_LISTS);
        $tpl->assign('stylesheet_exception',STYLE_PATH."/".CSS_LOGIN);
        $tpl->assign('authorisation_script',"../_main/auth.php");
        $tpl->assign('names',$DB->query("SELECT uname,longname FROM sys_groups",PDO::FETCH_NUM));
        $tpl->display_error("framework_oeelogin.tpl");
    }
*/
} // EO mainlistPage
$list=new mainlistPage();
$list->rootdirs=$rootdirs;   

/**
* If no profile is set, the user must login
*/
if(!isset($_SESSION['profile'])) {   # Login first
    switch ($_SESSION['system']) {
    case 'td':  # Standard
    case 'home':
        $list->DisplayLogin();
        break;
    }} else {   # Login has been done
/**
* Show the list...
*/
    $DB=DBC::get();
    /**
    * If this is the first launch of the query ($_REQUEST), reset the ORDER BY SQL
    * If this is a refresh ($_SESSION)
    */
    if (empty($_REQUEST['query_name'])) {
        $result=$DB->query("SELECT * FROM sys_queries WHERE name='{$_SESSION['query_name']}'");
        $template=$result->fetch(PDO::FETCH_ASSOC);
    } else {
        unset($_SESSION['order_by']);
        $result=$DB->query("SELECT * FROM sys_queries WHERE name='{$_REQUEST['query_name']}'",PDO::FETCH_ASSOC); 
        $template=$result->fetch(PDO::FETCH_ASSOC);
    }
    switch ($_SESSION['system']) {
    case 'td':  # Standard
    case 'home':
        $list->DisplayPageSmarty($template['template_section'],$template['template_css']);
        break;
    } // EO switch system
}   
?>

 
