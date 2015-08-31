<style type="text/css">
body {
/*    font-family: arial, helvetica, geneva, sans-serif; 
*/
    font-family: Verdana, Geneva, sans-serif;
    font-size: 10px;    }
table {
    font-size: 10px;
    border-spacing: 1px;
    border-style: solid;
    line-height: normal;
}
td {
    color: darkgrey;
    padding: 1px;
    font-size: 14px;
}
td.FREE {
    color: white;
    background-color: darkgreen; }
td.OCCUPIED {
    color: black;
    background-color: cyan;
}    
</style>
<?php
/**
* Action: get a list of occupied HAMA 
* Reviewed @ 18/04/2015
* @package tabwindow
* @subpackage music
* @var mixed
*/
$stylesheet="CSS_SMARTY";
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
$HAMA=array("HAMANR"=>2,
            "0"=>array(1,390),
            "1"=>array(401,390),
            "2"=>array(801,100)
            );
$HAMAFULL=array();
/** HAMA1 
* 
*/
for ($hama=0; $hama<=$HAMA["HAMANR"]; $hama++) {
    echo "<table width='100%'>";
    $HAMAFULL[$hama]=false;
    for ($i=$HAMA[$hama][0];$i<=$HAMA[$hama][0]+$HAMA[$hama][1]-1;$i++) {
        $RecordingID=DBC::fetchColumn("SELECT RecordingID FROM records2 WHERE HAMA=$i",0);
        if (empty($RecordingID)) {
            echo "<tr><td width='15%' class='FREE'><I>HAMA <B>".$i."</B></td><td colspan=10 class='FREE'>is Free</I></td></tr>";        
            $HAMAFULL[$hama]=true;
        } else {
            $result=$DB->query("SELECT * FROM records WHERE RecordingID='{$RecordingID}'");
            $record=$result->fetch(PDO::FETCH_ASSOC);
            echo "<tr><td>HAMA <b>$i</b></td><td class='OCCUPIED'>".$record['Artist']." / ".$record['Title']."</td></tr>";
        }
    }
    echo "</table>";
    if (!$HAMAFULL[$hama]) {
        echo "<B>HAMA_BOX_".($hama+1)." is completely filled</B><BR>";
    }
} // EO for
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
echo DBC::fetchcolumn("SELECT MAX(Classification) AS 'HAMA' FROM records",0);
?>
