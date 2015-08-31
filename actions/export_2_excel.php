<?php
/**
* Export results from query to an EXCEL file in the documents/export directory.
* 
* @author  Werner Huysmans
* @access  public
* @package framework
* @filesource
*/
require("../includes/config_mycmms.inc.php");
// error_reporting(E_ALL);
date_default_timezone_set('Europe/London');
$excel_filename=$rootdirs['docs'].$_SESSION['query_name']."_".str_pad(rand(0,9999),4,"0").'.xlsx';
$columns=array("-","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"); // Translation of columns into letters
$messages=array(); // Capturing progress messages

/**
* Get the SQL SELECT statement that must be changed
* The result is a screen with information and a download link to the generated EXCEL
* @param mixed $query
*/
function get_data_sql($query) {
    $DB=DBC::get();
    $sql=DBC::fetchcolumn("SELECT mysql FROM sys_queries WHERE name='$query'",0);
    eval('$sql="'.$sql.'";');
    return $sql;
}
$DB=DBC::get();
$sql=get_data_sql($_SESSION['query_name']);
$st=$DB->query($sql,PDO::FETCH_ASSOC);
$column_count=$st->columnCount();
for ($i=0; $i <= $column_count; $i++) {
    $meta[$i]=$st->getColumnMeta($i);
    $metatype[$i]=$meta[$i]["native_type"];
}
$result=$DB->query($sql,PDO::FETCH_ASSOC);

/** PHPExcel */
require_once 'EXCEL_TOOLS/Classes/PHPExcel.php';
$messages[0]['DATE']=date('H:i:s'); $messages[0]['MSG']="Create new PHPExcel object\n<br>";
$objPHPExcel = new PHPExcel();
$messages[1]['DATE']=date('H:i:s'); $messages[1]['MSG']="Set properties\n<br>";
$objPHPExcel->getProperties()->setCreator("Werner Huysmans")
                             ->setLastModifiedBy("Werner Huysmans")
                             ->setTitle("Office 2007 XLSX exported by myCMMS")
                             ->setSubject("Office 2007 XLSX content description")
                             ->setDescription("Exported by myCMMS.")
                             ->setKeywords("MYCMMS")
                             ->setCategory("MYCMMS EXPORT");
// Add some data
$messages[2]['DATE']=date('H:i:s'); $messages[2]['MSG']="Add some data\n<br>";
$row=1; 
foreach ($result->fetchAll(PDO::FETCH_NUM) as $record) {
    $column=1;
    foreach ($record as $fieldvalue) {
        $CellName=$columns[$column].$row;   // Convert the row/column into an EXCEL reference e.g. A1
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($CellName,$fieldvalue);
        $column++;
    }
    $row++;
}
$messages[3]['DATE']=date('H:i:s'); $messages[3]['MSG']="Rename sheet\n<br>";
$objPHPExcel->getActiveSheet()->setTitle('MYCMMS');
$objPHPExcel->setActiveSheetIndex(0);
$messages[4]['DATE']=date('H:i:s'); $messages[4]['MSG']="Write to Excel2007 format\n<br>";
$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($excel_filename);
$messages[5]['DATE']=date('H:i:s'); $messages[5]['MSG']="Peak memory usage: ".(memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n<br>";
$messages[6]['DATE']=date('H:i:s'); $messages[6]['MSG']="Done writing file.\r\n<br>";

require("setup.php");
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
$tpl->assign("meta",$meta);
$tpl->assign("query_name",$selection);
$tpl->assign("messages",$messages);
$tpl->assign("excel_filename",$excel_filename);
$tpl->assign("session",$_SESSION);
$tpl->assign("wikipage","Printout ALL work orders");
$tpl->display_error("export_2_excel.tpl");
?>
