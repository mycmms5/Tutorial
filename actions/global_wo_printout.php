<?php
/** 
* Printout all checked workorders
* 
* @author  Werner Huysmans
* @access  public
* @package work
* @subpackage printout_all
* @filesource
* CVS
* $Id: global_wo_printout.php,v 1.3 2013/04/17 05:40:09 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/global_wo_printout.php,v $
* $Log: global_wo_printout.php,v $
* Revision 1.3  2013/04/17 05:40:09  werner
* TEMP
*
*/ 
require("../includes/config_mycmms.inc.php");
unset($_SESSION['PDO_ERROR']);
require("setup.php");
$DB=DBC::get();

switch ($_REQUEST['STEP']) {
case "1": { 
    # DB Actions
    $result=$DB->query("SELECT WONUM,EQNUM,SCHEDSTARTDATE,TASKDESC FROM wo WHERE WOSTATUS='PL' AND EQNUM LIKE '{$_REQUEST['EQNUM']}%' ORDER BY EQNUM");
    $data=$result->fetchAll(PDO::FETCH_ASSOC);
    
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('data',$data);
    $tpl->display_error("global_wo_printout_list.tpl");
    break;
}
default: {
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('lines',$DB->query("SELECT EQLINE AS 'id',EQLINE AS 'text' FROM tbl_eqline",PDO::FETCH_NUM));
    $tpl->display_error("global_wo_printout_form.tpl");
    break;
}
}
?>