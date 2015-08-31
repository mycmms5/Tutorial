<?php
$stylesheet="CSS_SMARTY";
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
$HAMA=array("HAMANR"=>3,
            "0"=>1,
            "1"=>48,
            "2"=>200,
            "3"=>260);
$HAMAFULL=array(false,false,false);
/** HAMA1 
* 
*/
for ($hama=1; $hama<=$HAMA["HAMANR"]; $hama++) {
    for ($i=$HAMA[$hama-1]+1;$i<=$HAMA[$hama];$i++) {
    $result=$DB->query("SELECT ID FROM cd_mag2 WHERE HAMA=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "<I>HAMA <B>".$i."</B> is available</I><BR>";        
        $HAMAFULL[$hama]=true;
    } 
    }
    if (!$HAMAFULL[$hama]) {
        // DebugBreak();
        echo "<B>HAMA$hama is completely filled</B><BR>";
    }
    echo "<hr>";
}
/**
for ($i=1;$i<=200;$i++) {
    $result=$DB->query("SELECT RecordingID,Title FROM records WHERE Classification=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "HAMA ".$i." is available<BR>";        
    }
}
echo "<hr>";
for ($i=201;$i<=300;$i++) {
    $result=$DB->query("SELECT RecordingID,Title FROM records WHERE Classification=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "HAMA ".$i." is available<BR>";        
    }
}
echo "<hr>";
for ($i=301;$i<=500;$i++) {
    $result=$DB->query("SELECT RecordingID,Title FROM records WHERE Classification=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "HAMA ".$i." is available<BR>";        
    }
}
echo "<hr>";
**/
echo DBC::fetchcolumn("SELECT MAX(Classification) AS 'HAMA' FROM cd_mag",0);
?>
