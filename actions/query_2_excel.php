<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2011 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * CVS
 * @version $Id: query_2_excel.php,v 1.2 2013/04/17 05:52:11 werner Exp $   
 * $Source: /var/www/cvs/mycmms40/mycmms40/actions/query_2_excel.php,v $
 * $Log: query_2_excel.php,v $
 * Revision 1.2  2013/04/17 05:52:11  werner
 * Inserted CVS variables Id,Source and Log
 *
 */

/** Error reporting */
require("../includes/config_mycmms.inc.php");  
error_reporting(E_ALL);
date_default_timezone_set('Europe/London');
$excel_filename=$_SESSION['query_name']."_".str_pad(rand(0,9999),4,"0").'.xlsx';
$columns=array("-","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O");

$DB=DBC::get();
$result=$DB->query($_REQUEST['query_name'],PDO::FETCH_ASSOC);
/** PHPExcel */
require_once 'EXCEL_TOOLS/Classes/PHPExcel.php';
// Create new PHPExcel object
echo date('H:i:s') . " Create new PHPExcel object\n<br>";
$objPHPExcel = new PHPExcel();
// Set properties
echo date('H:i:s') . " Set properties\n<br>";
$objPHPExcel->getProperties()->setCreator("Werner Huysmans")
							 ->setLastModifiedBy("Werner Huysmans")
							 ->setTitle("Office 2007 XLSX exported by myCMMS")
							 ->setSubject("Office 2007 XLSX content description")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
// Add some data
echo date('H:i:s') . " Add some data\n<br>";
$objPHPExcel->setActiveSheetIndex(0);
$row=1; 
foreach ($result->fetchAll(PDO::FETCH_NUM) as $record) {
    $column=1;
    foreach ($record as $fieldvalue) {
        $cell=$columns[$column].$row;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell,$fieldvalue);
        $column++;
    }
    $row++;
}

/* $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!')
            ->setCellValue('C1', 'Hello')
            ->setCellValue('D2', 'world!');
            */
// Miscellaneous glyphs, UTF-8
/** $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A4', 'Miscellaneous glyphs')
            ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
*/            
// Rename sheet
echo date('H:i:s') . " Rename sheet\n<br>";
$objPHPExcel->getActiveSheet()->setTitle('MYCMMS');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
// Save Excel 2007 file
echo date('H:i:s') . " Write to Excel2007 format\n<br>";
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($excel_filename);
// Echo memory peak usage
echo date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n<br>";
// Echo done
echo date('H:i:s') . " Done writing file.\r\n<br>";
?>
<a href="<?PHP echo basename($excel_filename); ?>" target="output">view EXCEL</a>