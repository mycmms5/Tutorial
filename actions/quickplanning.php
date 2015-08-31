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
* CVS
* $Id: quickplanning.php,v 1.2 2013/04/17 05:52:11 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/quickplanning.php,v $
* $Log: quickplanning.php,v $
* Revision 1.2  2013/04/17 05:52:11  werner
* Inserted CVS variables Id,Source and Log
*
*/ 
require("../includes/config_mycmms.inc.php");
require("class_inputPageSmarty.php");

class thisPage extends inputPageSmarty {
public function page_content() {
    $DB=DBC::get();
    $sql="SELECT wo.*,DATEDIFF(REQUESTDATE,NOW()) AS 'DELAY1',DATEDIFF(SCHEDSTARTDATE,NOW()) AS 'DELAY2' FROM wo WHERE WOSTATUS='P' ORDER BY REQUESTDATE DESC";
    $result=$DB->query($sql);
    $data=$result->fetchAll(PDO::FETCH_ASSOC);
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('wos',$data);
    $tpl->assign('options',$DB->query("SELECT uname FROM sys_groups WHERE profile=64",PDO::FETCH_NUM));
    $tpl->assign('status',$status);
    $tpl->assign('actual_id',$_REQUEST['ID']);
    try {
        $tpl->display_error('quickplanning_form.tpl');    
    } catch (exception $e) {
        trigger_error($e.message, E_ERROR);
        echo $e.message;
    }
}
function process_form() {
    $DB=DBC::get();
    switch ($_REQUEST['ACTION']) {
    case "UPDATE":
        try {
            $DB->beginTransaction();
            DBC::execute("DELETE FROM woe WHERE WONUM=:wonum AND OPNUM=10 AND WODATE<NOW() AND REGHRS IS NULL",array("wonum"=>$_REQUEST['ID']));
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

/**
    for ($c=0; $c < sizeof($_REQUEST['wonum']); $c++) {
        $wo=$_REQUEST['wonum'][$c];
        $sd=$_REQUEST['schedstartdate'][$wo];
        DBC::execute("CALL DUMMY_WO_QPLAN(:wonum,:schedstartdate,:assignedtech,'PLAN')",array("wonum"=>$wo,"schedstartdate"=>$sd,"assignedtech"=>"MULTI"));
        foreach($_REQUEST['assignedtech'][$wo] as $tech) {
            DBC::execute("INSERT INTO woe (WONUM,OPNUM,EMPCODE,WODATE,ESTHRS) VALUES (:wonum,10,:empcode,:wodate,1)",array("wonum"=>$wo,"empcode"=>$tech,"wodate"=>$sd));
        }     
    } // EOF for
    $tpl->assign('wos',$DB->query("SELECT WONUM,EQNUM,TASKDESC,REQUESTDATE,SCHEDSTARTDATE,ASSIGNEDTECH,NULL FROM wo WHERE WOSTATUS='PL' ORDER BY SCHEDSTARTDATE DESC,EQNUM",PDO::FETCH_ASSOC));
    $tpl->display_error("global_quickplanning_list1.tpl");
    break;
}
default: {
    $techs=array(
        array("CAVENAILLEN","CAVENAILLEN"),
        array("LESCEUXA","LESCEUXA"),
        array("EVRAERTJL","EVRAERTJL"),
        array("GRIGOLATOA","GRIGOLATOA"),
        array("GODERNIAUXG","GODERNIAUXG"),
        array("DUBOISM","DUBOISM"),
        array("EXTERN","EXTERN"));

    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('stylesheet_calendar',STYLE_PATH."/".CSS_CALENDAR);
    $tpl->assign('status',$status);
    // $tpl->assign('techs',$techs);
    $tpl->assign('techs',$DB->query("SELECT uname AS 'id',uname AS 'text' FROM sys_groups WHERE profile=64",PDO::FETCH_NUM));
    $tpl->assign('recent_work',DBC::fetchcolumn("SELECT DATE_ADD(NOW(),INTERVAL -1 DAY) AS 'NEW'",0));
    $tpl->assign('wos',$DB->query("SELECT WONUM,EQNUM,WOSTATUS,TASKDESC,REQUESTDATE,SCHEDSTARTDATE,ASSIGNEDTECH,NULL,DATEDIFF(REQUESTDATE,PREPAREDDATE) AS 'DELAY1',DATEDIFF(SCHEDSTARTDATE,NOW()) AS 'DELAY2' FROM wo WHERE WOSTATUS='$STATUS' ORDER BY EQNUM",PDO::FETCH_ASSOC));
    $tpl->display_error("global_quickplanning_form.tpl");
}
}
**/
?>

