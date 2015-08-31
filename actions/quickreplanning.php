<?php
/** 
* Quick Work Planning
* 
* @author  Werner Huysmans 
* @access  public
* @version: 4.0
* @package work
* @subpackage planning
* @filesource
* @todo Migrate to Smarty
* @todo Use transaction
* CVS
* $Id: quickreplanning.php,v 1.2 2013/04/17 05:52:11 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/quickreplanning.php,v $
* $Log: quickreplanning.php,v $
* Revision 1.2  2013/04/17 05:52:11  werner
* Inserted CVS variables Id,Source and Log
*
*/ 
require("../includes/config_mycmms.inc.php");
require("class_inputPageSmarty.php");

class thisPage extends inputPageSmarty {
public function page_content() {
    $DB=DBC::get();
    $sql="SELECT wo.*,DATEDIFF(REQUESTDATE,NOW()) AS 'DELAY1',DATEDIFF(SCHEDSTARTDATE,NOW()) AS 'DELAY2' FROM wo WHERE WOSTATUS='PL' ORDER BY SCHEDSTARTDATE DESC";
    $result=$DB->query($sql);
    $data=$result->fetchAll(PDO::FETCH_ASSOC);
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    if ($_REQUEST['ID']!=null) {
        $tpl->assign("woes",$DB->query("SELECT uname,EMPCODE,woe.WONUM FROM sys_groups sg LEFT JOIN woe ON sg.uname=woe.EMPCODE AND woe.WONUM='{$_REQUEST['ID']}' AND OPNUM=10 AND REGHRS IS NULL WHERE sg.profile=64",PDO::FETCH_NUM));
        $_SESSION['PAGE_LOCATION']=$_REQUEST['ID'];
    }
    $tpl->assign('wos',$data);
    $tpl->assign('actual_id',$_REQUEST['ID']);
    try {
        $tpl->display_error('quickreplanning_form.tpl');    
    } catch (exception $e) {
        trigger_error($e.message, E_ERROR);
        echo $e.message;
    }
}
function process_form() {
    $DB=DBC::get();
    switch ($_REQUEST['ACTION']) {
    case "UPDATE":
        DBC::execute("DELETE FROM woe WHERE WONUM=:wonum AND WODATE<NOW() AND REGHRS IS NULL",array("wonum"=>$_REQUEST['ID']));
        try {
            $DB->beginTransaction();
            foreach ($_REQUEST['EMPCODE'] as $empcode) {
                DBC::execute("INSERT INTO woe (WONUM,OPNUM,EMPCODE,WODATE,ESTHRS) VALUES (:wonum,10,:empcode,:wodate,1)",array("wonum"=>$_REQUEST['ID'],"empcode"=>$empcode,"wodate"=>$_REQUEST['SCHEDSTARTDATE']));
            } // EO foreach
            DBC::execute("UPDATE wo SET SCHEDSTARTDATE=:schedstartdate,WOSTATUS='PL' WHERE WONUM=:wonum",array("wonum"=>$_REQUEST['ID'],"schedstartdate"=>$_REQUEST['SCHEDSTARTDATE']));    
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        } // EO catch      
        break;
    default:
        break;                           
    }
} // End process_form
} // End of class

$inputPage=new thisPage();
$inputPage->flow();
?>

