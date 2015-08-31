<?php
/** 
* Import preformatted text file generated via Outlook
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @version 4.0 201106
* @access  public
* @package tools
* @subpackage gettext in smarty templates
* @filesource
* @tpl  No Template
* @txid No Transaction
*/
error_reporting(E_ALL ^ E_NOTICE);

function import_action($filename) {
    global $doc_paths;
    $DB=DBC::get();
    DBC::execute("DELETE FROM gettext_smarty",array());
    $importfile=$doc_paths['import'].$filename;
    $fh=fopen($importfile,"r") or die ("$filename couldn't be opened");
    $translations=file($importfile);  // Load all information into array
    fclose($fh);
    
    // Empty array for all information
    $gettext=array(); $j=0;   
    
    for($i=0; $i< count($translations);$i++) {
        if(substr($translations[$i],0,22)=="/* ../smarty/templates") {
            $sourcefile=trim(substr($translations[$i],23));
            $gettext[$j]['TEMPLATE'].=substr($sourcefile,0,strlen($sourcefile)-3);
        }
        if(substr($translations[$i],0,8)=="gettext(") {
            $SOR=true;
            $msgid=trim(substr($translations[$i],9),"\"");
            $gettext[$j]['ORIGINAL']=substr($msgid,0,strlen($msgid)-4);
            
            DBC::execute("INSERT IGNORE INTO gettext_smarty (SMARTY,ORIGINAL) VALUES (:smarty,:original)",
                array(
                    "original"=>$gettext[$j]['ORIGINAL'],
                    "smarty"=>$gettext[$j]['TEMPLATE']));
            $j++;
        }
    } // EO for

    print_r($gettext);   
}
?>
