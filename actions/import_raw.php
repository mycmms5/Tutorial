<?php
/** 
* Import preformatted EXCEL sheet for PPM task informations with the PHPEcxel library
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package ppm
* @subpackage import
* @filesource
* CVS
* $Id: import_raw.php,v 1.1 2013/05/12 08:02:48 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/import_raw.php,v $
* $Log: import_raw.php,v $
* Revision 1.1  2013/05/12 08:02:48  werner
* NEW import EXCEL and show as a table
*
*/
error_reporting(E_ERROR);

function import_action($filename) {
    require_once 'EXCEL_TOOLS/Classes/PHPExcel/IOFactory.php';
    $objReader=PHPExcel_IOFactory::createReader('Excel2007');
    $objReader->setLoadSheetsOnly("Kalender");
    $objReader->setReadDataOnly(true);
/**
* C:\Program Files (x86)\NuSphere\TechPlat\apache\htdocs\common\documents_DEMB\import
*     
* @var mixed
*/
    $objPHPExcel=$objReader->load(WAMP_DIR.DOC_PATH."import/".$filename);
    $objWorksheet=$objPHPExcel->getActiveSheet();
    $lastRow=$objWorksheet->getHighestRow();
    $lastCol=$objWorksheet->getHighestColumn();
    $lastColIndex=PHPExcel_Cell::columnIndexFromString($lastCol);
    
    for ($row=1; $row <= $lastRow; ++$row) {
        for ($col=0; $col <= $lastColIndex; ++$col) {
            $data[$row][$col]=$objWorksheet->getCellByColumnAndRow($col,$row)->getValue();
        }
    }
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("data",$data);
    $tpl->display_error("import_raw.tpl");  
}
?>
