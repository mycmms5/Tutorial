<?php
/** 
* Import preformatted text file generated via Outlook
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @version 4.0 201106
* @access  public
* @package tools
* @subpackage gettext
* @filesource
* @tpl  No Template
* @txid No Transaction
*/
error_reporting(E_ALL ^ E_NOTICE);

function import_action($filename) {
    global $doc_paths;
    $DB=DBC::get();
    if($filename == "MYCMMS40_UK.PO") {
        DBC::execute("DELETE FROM gettext",array());
    }
    $importfile=$doc_paths['import'].$filename;
    $fh=fopen($importfile,"r") or die ("$filename couldn't be opened");
    $translations=file($importfile);  // Load all information into array
    fclose($fh);
    
    // Empty array for all information
    $gettext=array(); $j=0;   
    
    for($i=0; $i< count($translations);$i++) {
        if(substr($translations[$i],0,9)=="#: myCMMS") {
            $sourcefile=trim(substr($translations[$i],10))."\n";
            $gettext[$j]['FILES'].=$sourcefile;
        }
        if(substr($translations[$i],0,5)=="msgid") {
            $msgid=trim(substr($translations[$i],7));
            $msgid=str_replace('"','',$msgid);
            $gettext[$j]['ORIGINAL']=$msgid;
        }
        if(substr($translations[$i],0,6)=="msgstr") {
            $SOR=true;
            $msgstr=trim(substr($translations[$i],8),"\"");
            $gettext[$j]['TRANSLATION']=str_replace('"','',$msgstr);
            
            if($filename == "MYCMMS40_UK.PO") {
                DBC::execute("INSERT IGNORE INTO gettext (ORIGINAL,EN_GB,FILES) VALUES (:original,:en_gb,:files)",
                array(
                    "original"=>$gettext[$j]['ORIGINAL'],
                    "en_gb"=>$gettext[$j]['TRANSLATION'],
                    "files"=>$gettext[$j]['FILES']));
            } 
            if($filename == "MYCMMS40_NL.PO") {
                DBC::execute("UPDATE gettext SET NL_NL=:nl_nl WHERE ORIGINAL=:original",
                    array(
                    "original"=>$gettext[$j]['ORIGINAL'],
                    "nl_nl"=>$gettext[$j]['TRANSLATION']));
            }
            if($filename == "MYCMMS40_FR.PO") {
                DBC::execute("UPDATE gettext SET FR_FR=:fr_fr WHERE ORIGINAL=:original",
                    array(
                    "original"=>$gettext[$j]['ORIGINAL'],
                    "fr_fr"=>$gettext[$j]['TRANSLATION']));
            }

            $j++;
            $gettext[$j]['FILES']="";
        }
    } // EO for

    print_r($gettext);   
}
?>
