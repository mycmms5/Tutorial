<?PHP
/** 
* @author  Werner Huysmans 
* @access  public
* @package DEBUG
* @subpackage BETA
* @filesource
* CVS
* $Id: betastuff.php,v 1.2 2013/04/17 05:44:52 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/betastuff.php,v $
* $Log: betastuff.php,v $
* Revision 1.2  2013/04/17 05:44:52  werner
* Inserted CVS variables Id,Source and Log
*

*/
require("../includes/config_mycmms.inc.php");
require("HTML/Table.php");
// require(CMMS_LIB."/class_actionPage.php");
require("lib_queries.php");

switch ($_REQUEST['STEP']) {
case "1": { 
    $DB=DBC::get();
    $labels=array(
    "HEADER"=>"Adding LIST or ACTION into B?ta Testing",
    "DBFLD_name"=>_("DBFLD_name"),
    "DBFLD_caption"=>_("DBFLD_caption"),
    "DBFLD_mysql"=>_("DBFLD_mysql"),
    "DBFLD_MENUORDER"=>_("DBFLD_MENUORDER")
    );
    
    try {
        $DB->beginTransaction();
        DBC::execute("INSERT INTO sys_queries (name,mode,caption,title,profile,mysql) VALUES (:name,'none',:caption,:caption,1,:mysql)",array("name"=>$_REQUEST['NAME'],"caption"=>$_REQUEST['CAPTION'],"mysql"=>$_REQUEST['MYSQL']));
        $menuorder=DBC::fetchcolumn("SELECT MAX(MENUORDER) FROM sys_navigation WHERE NAV='tools' AND CAT='BETA'",0);
        DBC::execute("INSERT INTO sys_navigation (NAV,CAT,LINK,MENUORDER) VALUES ('tools','BETA',:name,:menuorder)",array("name"=>$_REQUEST['NAME'],"menuorder"=>$menuorder+1));
        $DB->commit();
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    } 
    
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->debugging=true;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('labels',$labels);
    # data
    $result=$DB->query("SELECT sn.LINK,sn.MENUORDER,sq.caption,sq.mysql FROM sys_navigation sn INNER JOIN sys_queries sq ON sn.LINK=sq.name WHERE sn.NAV='tools' AND sn.CAT='BETA' ORDER BY sn.MENUORDER");
    $tpl->assign('actions',$result->fetchAll(PDO::FETCH_ASSOC));
    $tpl->display_error("betastuff_list.tpl");
    break;
} // EO STEP1
default: {
    $DB=DBC::get();
    $labels=array(
    "HEADER"=>"Adding LIST or ACTION into B?ta Testing",
    "command"=>"Add to TOOLS tab"
    );
    
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('labels',$labels); 
    $tpl->display_error("betastuff_form.tpl");
    break;
} // EO default
} // EO Switch
?>