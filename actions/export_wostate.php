<?php
/** 
* Import preformatted EXCEL sheet for PPM task informations
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package ppm
* @subpackage import
* @filesource
* CVS
* $Id: export_wostate.php,v 1.1 2013/07/30 08:49:42 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/export_wostate.php,v $
* $Log: export_wostate.php,v $
* Revision 1.1  2013/07/30 08:49:42  werner
* DEMB reading and writing to EXCEL2007
*
* Revision 1.2  2013/04/17 05:44:53  werner
* Inserted CVS variables Id,Source and Log
*
*/
error_reporting(E_ERROR);
ini_set('memory_limit', '1024M');
require_once 'Spreadsheet/Excel/reader.php';

function export_action($outputFilename) {
    global $doc_paths;
/** MySQL
* All statements to get the data
*/
    $DB=DBC::get();
    $wostatus=array();
    $wostatus['R']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='R'",0);
    $wostatus['A']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='A'",0);
    $wostatus['M']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='M'",0);
    $wostatus['P']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='P'",0);
    $wostatus['PL']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='PL'",0);
    $wostatus['PR']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='PR'",0);
    $wostatus['F']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='F'",0);
    $wostatus['C']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE WOSTATUS='C'",0);
    $wostatus['NEW']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE DATE(REQUESTDATE)=DATE(NOW()) AND PRIORITY>1",0);
    $wostatus['END']=DBC::fetchcolumn("SELECT COUNT(*) FROM wo WHERE DATE(COMPLETIONDATE)=DATE(NOW()) AND PRIORITY>1",0);
/** PHPExcel 
* Reader to retrieve the next available row
*/
    require_once 'EXCEL_TOOLS/Classes/PHPExcel/IOFactory.php';
    $objReader = new PHPExcel_Reader_Excel2007(); 
    $objPHPExcel = $objReader->load($doc_paths['export'].$outputFilename); 
    $objWorksheet = $objPHPExcel->getSheetByName("DATA");
    $nextRow=$objWorksheet->getCell("Q1")->getValue();
    if (!is_null($nextRow)) {
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $Now=getdate();
        $objPHPExcel->getSheetByName("DATA")->setCellValue("A".$nextRow,$Now['year']."/".$Now['mon']."/".$Now['mday']." ".$Now['hours'].":".$Now['minutes']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("B".$nextRow,$wostatus['R']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("C".$nextRow,$wostatus['A']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("D".$nextRow,$wostatus['M']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("E".$nextRow,$wostatus['P']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("F".$nextRow,$wostatus['PL']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("G".$nextRow,$wostatus['PR']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("H".$nextRow,$wostatus['F']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("I".$nextRow,$wostatus['C']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("J".$nextRow,$wostatus['NEW']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("K".$nextRow,$wostatus['END']);
        $objPHPExcel->getSheetByName("DATA")->setCellValue("Q1",$nextRow+1);
        $objWriter->save($doc_paths['export'].$outputFilename); 
    } else {
        $nextRow="Error: couldn't find the nextRow at Cell Q1";
    }
    $objPHPExcel->disconnectWorksheets();
    unset($objPHPExcel);
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("filename",$outputFilename);
    $tpl->assign("nextRow",$nextRow);
    $tpl->assign("wostatus",$wostatus);
    $tpl->display_error("export_wostate.tpl");  
}
?>
