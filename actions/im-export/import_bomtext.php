<?php
error_reporting(E_ALL ^ E_NOTICE);

function import_action($filename) {
    global $doc_paths;
    $DB=DBC::get();
    $importfile=$doc_paths['import'].$filename;
    $fh=fopen($importfile,"r") or die ("$filename couldn't be opened");
    $issues=file($importfile);  // Load all information into array
    fclose($fh);
    
    for($i=0; $i< count($issues);$i++) {
        $n=substr_count($issues[$i],"|",0);
        if (strpos($issues[$i],'|--  ')) {
            $bom[$n]=$issues[$i];
        }
        if (strpos($issues[$i],'|---   540')) {
            echo $bom[$n]." : ".$issues[$i]."<br>";
        }
    } // EO for
   
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->assign('notifications',$notifications);
    // $tpl->display_error("import_notifications_created.tpl");
}  
?>
