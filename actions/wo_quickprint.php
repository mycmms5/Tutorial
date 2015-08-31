<?php
/** 
* @author  Werner Huysmans 
* @access  public
* @package mycmms40
* @subpackage REMACLE
* @filesource
*/
require("../includes/config_mycmms.inc.php");
require("setup.php");
require("lib_queries.php");
$DB=DBC::get();

switch ($_REQUEST['STEP']) {
case "1": { 
    require("HTML2PDF/html2fpdf.php");
    $pdf=new HTML2FPDF();
    if (isset($_REQUEST['WORKDAY1'])) { # Workers in Floriffoux
        $WS_DAY=$_REQUEST['WORKDAY1'];
        $WS_toPrint=$_REQUEST['uname1']; 
    }
    if (isset($_REQUEST['WORKDAY2'])) { # Workers in Malonne
        $WS_DAY=$_REQUEST['WORKDAY2']; 
        $WS_toPrint=$_REQUEST['uname2']; 
    }
    $qp=fopen("Printout_WOQ.html","w");
    foreach($WS_toPrint as $TECH) {
        DBC::execute("SET NAMES 'latin1'",array());
        $data=$DB->query("SELECT CONCAT(equip.DESCRIPTION,'<BR>',COSTCENTER),DATE_FORMAT(WODATE,'%d-%M'),'0',CONCAT(wo.WONUM,'<BR>',TASKDESC) FROM wo LEFT JOIN equip ON wo.EQNUM=equip.EQNUM LEFT JOIN woe ON wo.WONUM=woe.WONUM WHERE EMPCODE='$TECH' AND WODATE='$WS_DAY'",PDO::FETCH_NUM);
        $REMACLE_ID=DBC::fetchcolumn("SELECT vs FROM sys_groups WHERE uname='$TECH'",0);
        
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->assign('data',$data);
        $tpl->assign('tech',$TECH);
        $tpl->assign('id',$REMACLE_ID);
        try {
            $html_content=$tpl->fetch("printout_wo_quick.tpl");
            fwrite($qp,$html_content);
        } catch (exception $e) {
            trigger_error($e.message, E_ERROR);
            echo $e.message;
        }
        $pdf->AddPage();
        $pdf->WriteHtml($html_content);
    }
    $pdf_filename="Printout_woq_".str_pad(rand(0,9999),4,"0").'.pdf';
    $pdf->Output($pdf_filename);    
    fclose($qp);
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('pdf_filename',$pdf_filename);
    $tpl->assign('techs',$DB->query("SELECT uname,longname FROM sys_groups WHERE profile & 64 <>0",PDO::FETCH_ASSOC));
    try {
        $tpl->display("wo_quickprint_pdf.tpl");
    } catch (exception $e) {
        trigger_error($e.message, E_ERROR);
        echo $e.message;
    }
    break;
}
default: {
    $tpl=new smarty_mycmms();
    $tpl->debugging=false;
    $tpl->caching=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('techs_flo',$DB->query("SELECT uname,longname FROM sys_groups WHERE profile=64 AND dept='CRH-FLO'",PDO::FETCH_ASSOC));
    $tpl->assign('techs_mal',$DB->query("SELECT uname,longname FROM sys_groups WHERE profile=64 AND dept='CRH-ML'",PDO::FETCH_ASSOC));
    try {
    $tpl->display("wo_quickprint_form.tpl");
    } catch (exception $e) {
        trigger_error($e.message, E_ERROR);
        echo $e.message;
    }
}
}
?>
