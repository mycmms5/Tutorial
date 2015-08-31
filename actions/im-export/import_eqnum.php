<?php
/**
* Import SPARES table from an EXCEL generated by SAP
* 
* @author  Werner Huysmans 
* @access  public
* @package warehouse
* @subpackage import
* @filesource* 
*/
error_reporting(E_ALL ^ E_NOTICE);
define("INIT",true);

function import_action($inputFileName) {
    require("../includes/config_mycmms.inc.php");
    set_time_limit(0);
    date_default_timezone_set('Europe/London');

    $DB=DBC::get();
    if (INIT) {
        DBC::execute("DELETE FROM equip_sap",array());
    }
/** PHPExcel 
* Select the EXCEL2007 format
* Find the highestRow to use it in the loop
*/
    require_once 'EXCEL_TOOLS/Classes/PHPExcel/IOFactory.php';
    $inputFileType="Excel2007";
    $objReader = PHPExcel_IOFactory::createReader($inputFileType); 
    $objPHPExcel = $objReader->load($doc_paths['import'].$inputFileName); 
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
    $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
/**
* Adressing columns happens with letters, the array will translate a column(1) into A
*/
    $alphabet=array("A","B","C","D","E","F","G","H","I","J","K","L","M");
    $eqnum=array();
    $records=array(
        "new"=>0,
        "changed"=>0,
        "deleted"=>0,
        "spare"=>0 );
/**
* Loop through all rows in the Excel sheet
*/
    for ($i=6; $i<=$highestRow; $i++) {
/** 
* Handle new EQNUM
*/
    if ($objWorksheet->getCell("A".$i)->getValue()=="NEW") {
        $eqnum["TODO"]="NEW";
        $eqnum["EQNUM_OLD"]="NEW";
        $eqnum["EQNUM"]=$objWorksheet->getCell("D".$i)->getValue();
        $length=strlen($objWorksheet->getCell("D".$i)->getValue());
        $eqnum["EQROOT"]=substr($objWorksheet->getCell("D".$i)->getValue(),0,$length-3);   
        $eqnum["EQ_DESC"]=$objWorksheet->getCell("C".$i)->getValue()." ".$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["SPARECODE"]=$objWorksheet->getCell("G".$i)->getValue();
        try {
            $DB->beginTransaction();
            DBC::execute("INSERT INTO equip_sap (TODO,EQROOT,EQNUM,EQ_DESC,SPARECODE) 
                VALUES (:todo,:eqroot,:eqnum,:eq_desc,:sparecode)",
            array(
                "todo"=>$eqnum["TODO"],
                "eqroot"=>$eqnum["EQROOT"],
                "eqnum"=>$eqnum["EQNUM"],
                "eq_desc"=>$eqnum["EQ_DESC"],
                "sparecode"=>$eqnum["SPARECODE"]));
            $DB->commit();               
            $records["new"]++;
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e,"Error in line $i");
        }
    } // EO if NEW   
    if ($objWorksheet->getCell("A".$i)->getValue()=="EDIT") {
        $eqnum["TODO"]="EDIT";
        $eqnum["EQNUM_OLD"]=$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["EQNUM"]=$objWorksheet->getCell("D".$i)->getValue();
        $length=strlen($objWorksheet->getCell("D".$i)->getValue());
        $eqnum["EQROOT"]=substr($objWorksheet->getCell("D".$i)->getValue(),0,$length-3);   
        $eqnum["EQ_DESC"]=$objWorksheet->getCell("C".$i)->getValue()." ".$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["SPARECODE"]=$objWorksheet->getCell("G".$i)->getValue();
        try {
            $DB->beginTransaction();
            DBC::execute("INSERT INTO equip_sap (TODO,EQNUM_OLD,EQROOT,EQNUM,EQ_DESC,SPARECODE) 
                VALUES (:todo,:eqnum_old,:eqroot,:eqnum,:eq_desc,:sparecode)",
            array(
                "todo"=>$eqnum["TODO"],
                "eqnum_old"=>$eqnum["EQNUM_OLD"],
                "eqroot"=>$eqnum["EQROOT"],
                "eqnum"=>$eqnum["EQNUM"],
                "eq_desc"=>$eqnum["EQ_DESC"],
                "sparecode"=>$eqnum["SPARECODE"]));
            $DB->commit();
            $records["changed"]++;
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e,"Error in line $i");
        }
    } // EO if EDIT
    if ($objWorksheet->getCell("A".$i)->getValue()=="DELETE") {
        $eqnum["TODO"]="DEL";
        $eqnum["EQNUM"]=$objWorksheet->getCell("D".$i)->getValue();
        $eqnum["EQROOT"]="S103-DELETED";
        $eqnum["EQ_DESC"]=$objWorksheet->getCell("C".$i)->getValue()." ".$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["SPARECODE"]=$objWorksheet->getCell("G".$i)->getValue();
        try {
            $DB->beginTransaction();
            DBC::execute("INSERT INTO equip_sap (TODO,EQROOT,EQNUM,EQ_DESC,SPARECODE) 
                VALUES (:todo,:eqroot,:eqnum,:eq_desc,:sparecode)",
            array(
                "todo"=>$eqnum["TODO"],
                "eqroot"=>$eqnum["EQROOT"],
                "eqnum"=>$eqnum["EQNUM"],
                "eq_desc"=>$eqnum["EQ_DESC"],
                "sparecode"=>$eqnum["SPARECODE"]));
            $DB->commit();                
            $records["deleted"]++;
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e,"Error in line $i");
        } // EO try
    } // EO if DEL  
    if ($objWorksheet->getCell("A".$i)->getValue()=="SPARE") {
        $eqnum["TODO"]="SPARE";
        $eqnum["EQNUM_OLD"]=$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["EQNUM"]=$objWorksheet->getCell("D".$i)->getValue();
        $length=strlen($objWorksheet->getCell("D".$i)->getValue());
        $eqnum["EQROOT"]=substr($objWorksheet->getCell("D".$i)->getValue(),0,$length-3);   
        $eqnum["EQ_DESC"]=$objWorksheet->getCell("C".$i)->getValue()." ".$objWorksheet->getCell("B".$i)->getValue();
        $eqnum["SPARECODE"]=$objWorksheet->getCell("G".$i)->getValue();
        try {
            $DB->beginTransaction();
            DBC::execute("INSERT INTO equip_sap (TODO,EQNUM_OLD,EQROOT,EQNUM,EQ_DESC,SPARECODE) 
                VALUES (:todo,:eqnum_old,:eqroot,:eqnum,:eq_desc,:sparecode)",
            array(
                "todo"=>$eqnum["TODO"],
                "eqnum_old"=>$eqnum["EQNUM_OLD"],
                "eqroot"=>$eqnum["EQROOT"],
                "eqnum"=>$eqnum["EQNUM"],
                "eq_desc"=>$eqnum["EQ_DESC"],
                "sparecode"=>$eqnum["SPARECODE"]));
            $DB->commit();                
            $records["spare"]++;
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e,"Error in line $i");
        } // EO try
    } // EO if DEL  

    } // EO for
        
/** SMARTY */
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("HROW",$highestRow);
    $tpl->assign("HCOL",$highestColumn);
    $tpl->assign("header","Imported : ".$excel_filename);
    $tpl->assign("records",$records);
    $tpl->assign("wikipage","No WIKI information available");
    $tpl->display_error("import_eqnum.tpl");  
}
?>
