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
* $Id: import_ppmcalendar.php,v 1.2 2013/04/17 05:44:53 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/import_ppmcalendar.php,v $
* $Log: import_ppmcalendar.php,v $
* Revision 1.2  2013/04/17 05:44:53  werner
* Inserted CVS variables Id,Source and Log
*
*/
error_reporting(E_ERROR);
define("ERASE",false);    // Erase all records, clean start
define("SHEET",1);
define("TASKNUM",2);
define("EQNUM",4);
define("DATUM",6);
// Test CVS
require_once 'Spreadsheet/Excel/reader.php';
function convert_date($str) {
    $datestring=(substr($str,6,4).substr($str,3,2).substr($str,0,2));
    return $datestring;
}
function import_action($filename) {
    global $doc_paths;
    $DB=DBC::get();
    // Erase old PPM PLAN
    if (ERASE) {
        DBC::execute("DELETE FROM ppmcalendar",array());    
    }    
    // ExcelFile($filename, $encoding);
    $data = new Spreadsheet_Excel_Reader();
    // Set output Encoding.
    $data->setOutputEncoding('CP1251');
    /***
* if you want you can change 'iconv' to mb_convert_encoding:
* $data->setUTFEncoder('mb');
*
**/

/***
* By default rows & cols indeces start with 1
* For change initial index use:
* $data->setRowColOffset(0);
*
**/



/***
*  Some function for formatting output.
* $data->setDefaultFormat('%.2f');
* setDefaultFormat - set format for columns with unknown formatting
*
* $data->setColumnFormat(4, '%.3f');
* setColumnFormat - set format for column (apply only to number fields)
*
**/
    $importfile=$doc_paths['import'].$filename;
    $sheet_ToRead=SHEET;    
    $data->read($importfile);
    // PPM PLAN
    for ($i = 1; $i <= $data->sheets[$sheet_ToRead]['numRows']; $i++) {
        if ($data->sheets[$sheet_ToRead]['cells'][$i][1]=="DATUM") {
            $launch_dates=array();
            for ($d=DATUM; $d< 365; $d++) {
                $launch_dates[$d]=$data->sheets[$sheet_ToRead]['cells'][$i][$d];
            }
        }
        if ($data->sheets[$sheet_ToRead]['cells'][$i][1]=="INSERT") {
	        $tasks=array();
            $tasks[$i][1]=$data->sheets[$sheet_ToRead]['cells'][$i][TASKNUM];
            $tasks[$i][2]=$data->sheets[$sheet_ToRead]['cells'][$i][EQNUM];
            for ($d=DATUM; $d< 365; $d++) {
                if (!empty($data->sheets[$sheet_ToRead]['cells'][$i][$d])) {
                    echo $launch_dates[$d].": ".$tasks[$i][1]." / ".$tasks[$i][2]."<BR>";
                    try {
                        $DB->beginTransaction();
                        DBC::execute("INSERT IGNORE INTO ppmcalendar_test (TASKNUM,EQNUM,PLANDATE) VALUES (:tasknum,:eqnum,:plandate)",array("tasknum"=>$tasks[$i][1],"eqnum"=>$tasks[$i][2],"plandate"=>convert_date($launch_dates[$d]))); 
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
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("filename",$filename);
    $tpl->display_error("import_ppmcalendar.tpl");  
}
?>
