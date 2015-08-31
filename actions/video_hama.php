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
    font-size: 14px; }
td.OCCUPIED {
    color: black;
    background-color: cyan; }    
td.FREE {
    color: white;
    background-color: darkgreen; }
</style>
<?php
$stylesheet="CSS_PRINTOUT";
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
/** HAMA1 
* 
*/
$HAMA=array("HAMANR"=>5,
            "0"=>array(1,200),
            "1"=>array(201,200),
            "2"=>array(401,200),
            "3"=>array(601,200), 
            "4"=>array(801,200),
            "5"=>array(1001,200),
            "6"=>array(1201,200),
            "7"=>array(1401,200));
$HAMAFULL=array();
/** HAMA1 
* 
*/
for ($hama=0; $hama<=$HAMA["HAMANR"]; $hama++) {
    echo "<table width='100%'>";
    $HAMAFULL[$hama]=false;
    for ($i=$HAMA[$hama][0];$i<=$HAMA[$hama][0]+$HAMA[$hama][1]-1;$i++) {
        $VideoID=DBC::fetchColumn("SELECT VideoID FROM Video2 WHERE HAMA=$i",0);
        if (empty($VideoID)) {
            echo "<tr><td width='15%' class='FREE'><I>HAMA <B>".$i."</B></td><td colspan=10 class='FREE'>is FREE</I></td></tr>";        
            $HAMAFULL[$hama]=true;
        } else {
            $result=$DB->query("SELECT * FROM Video WHERE VideoID='{$VideoID}'");
            $video=$result->fetch(PDO::FETCH_ASSOC);
            echo "<tr><td>HAMA <b>$i</b></td><td class='OCCUPIED'>".$video['title']."</td></tr>";
        }
    }
    if (!$HAMAFULL[$hama]) {
        echo "<B>HAMA_BOX_".($hama+1)." is completely filled</B><BR>";
    }
}
/** HAMA2
* 
*/
/**
$bHAMA2=false;
for ($i=201;$i<=400;$i++) {
    $result=$DB->query("SELECT VideoID FROM Video2 WHERE HAMA=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "HAMA ".$i." is available<BR>";        
    }
}
if (!$bHAMA2) {
        echo "HAMA2 is completely filled<BR>";
}
echo "<hr>";
for ($i=401;$i<=600;$i++) {
    $result=$DB->query("SELECT VideoID FROM Video2 WHERE HAMA=$i");
    $row=$result->fetchColumn(0);
    if (empty($row)) {
        echo "HAMA ".$i." is available<BR>";        
    }
}
echo "<hr>";
**/
echo "First free DVD number = ".DBC::fetchcolumn("SELECT MAX(HAMA)+1 AS 'HAMA' FROM video2",0);
?>
