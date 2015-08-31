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
* $Id: import_ppmcalendar_2.php,v 1.2 2013/07/30 08:32:49 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/import_ppmcalendar_2.php,v $
* $Log: import_ppmcalendar_2.php,v $
* Revision 1.2  2013/07/30 08:32:49  werner
* sync
*
* Revision 1.1  2013/05/12 08:02:18  werner
* NEW import PPM calendar with PHPExcel tools, this replaces the import_ppmcalendar.php
*
*/
error_reporting(E_ERROR);
define("SHEET",1);
define("TASKNUM","B");
define("EQNUM","D");
define("DATUM",5);

function convert_date($str) {
    $datestring=(substr($str,6,4).substr($str,3,2).substr($str,0,2));
    return $datestring;
}
function import_action($filename) {
    require_once 'EXCEL_TOOLS/Classes/PHPExcel/IOFactory.php';
/**
* Define location, type and sheetname
*/
    $inputFileType="Excel2007";
    $sheetName="Kalender";
    $importFile=WAMP_DIR.DOC_PATH."import/".$filename;
    $locale = 'fr/FR';
    $validLocale = PHPExcel_Settings::setLocale($locale);
    try {
        $objReader=PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setLoadSheetsOnly($sheetName);
        $objPHPExcel=$objReader->load($importFile);
    } catch (Exception $e) {
        die('Error loading file: '.$e->getMessage());
    }
    $objWorksheet=$objPHPExcel->getActiveSheet();
    $lastRow=$objWorksheet->getHighestRow();
    $lastCol=$objWorksheet->getHighestColumn();
    $lastColIndex=PHPExcel_Cell::columnIndexFromString($lastCol);

    $DB=DBC::get();

/**
* Start reading the PPM Calendar
*/
    for ($i = 1; $i <= $lastRow; $i++) {
/**
* When row is labeled DATUM -> this row contains the launch dates
*/
        if ($objWorksheet->getCell('A'.$i)->getValue()=="DATUM") {
            $launch_dates=array();
            for ($d=0; $d< $lastColIndex; $d++) {
                $schedstartdate_timestamp=PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($d,$i)->getValue());
                $launch_dates[$d]=date("Y-m-d",$schedstartdate_timestamp);
//                $launch_dates[$d]=$objWorksheet->getCellByColumnAndRow($d,$i)->getValue();
            }
        }
/**
* When row is labeled INSERT -> this row contains the plan for TASKNUM + EQNUM        
*/
        if ($objWorksheet->getCell('A'.$i)->getValue()=="INSERT") {
	        $tasks=array();
            $tasknum=$objWorksheet->getCell(TASKNUM.$i)->getValue();
            $eqnum=$objWorksheet->getCell(EQNUM.$i)->getValue();
            for ($d=DATUM; $d< $lastColIndex; $d++) {
                $active=$objWorksheet->getCellByColumnAndRow($d,$i)->getValue();
                if (!empty($active)) {
                    $task[$i]['TASKNUM']=$tasknum;
                    $task[$i]['EQNUM']=$eqnum;
                    $task[$i]['LAUNCH'].=$launch_dates[$d]." + ";
                    try {
                        $DB->beginTransaction();
//                        DBC::execute("INSERT IGNORE INTO ppmcalendar_test (TASKNUM,EQNUM,PLANDATE) VALUES (:tasknum,:eqnum,:plandate)",array("tasknum"=>$tasks[$i][1],"eqnum"=>$tasks[$i][2],"plandate"=>convert_date($launch_dates[$d]))); 
                        $DB->commit();
                    } catch (exception $e) {
                        $DB->rollBack();
                        PDO_log($e);
                    }
                } // End if 1
            } // End for date loop
        } // End if INSERT
    } // End for loop
    
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->debugging=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("filename",$filename);
    $tpl->assign("data",$task);
    $tpl->display_error("import_ppmcalendar_2.tpl");  
}
?>
