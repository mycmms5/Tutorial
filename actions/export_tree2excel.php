<html>
<head>
<title></title>
<link href="../styles/smarty_base.css" rel="stylesheet" type="text/css" />
<style>
td.FL { background-color: lightblue;}
td.MOV { background-color: lightgreen;}
td.NEW { background-color: orange;}
</style>
</head>
<body>
<?php
$nosecurity_check=true;
$filter=$_REQUEST['VAR1'];
function get_children($postid,$fh) {
    $DB=DBC::get();
    $result=$DB->query("SELECT * FROM equip WHERE parent=$postid ORDER BY EQORDER");
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $level_string=str_repeat(";",$row['LEVEL']);
        fwrite($fh,$level_string.$row['EQNUM'].";".$row['DESCRIPTION'].";".$row['EQFL']."\n");
        $level_cols=str_repeat("<td></td>",$row["LEVEL"]);
        $level_colsend=str_repeat("<td></td>",10-$row["LEVEL"]);
        echo "<tr>".$level_cols."<td>".$row['EQNUM']."</td><td>".$row['DESCRIPTION']."</td>".$level_colsend."<td class='{$row['EQFL']}'>".$row['EQFL']."</td></tr>";
        if ($row['children']==1) {
            get_children($row['postid'],$fh);
        }
    }
}

    require("../includes/config_mycmms.inc.php");
    $DB=DBC::get();
    $export_filename=$rootdirs['docs'].$filter."_".str_pad(rand(0,9999),4,"0").".csv";
    $fh=fopen($export_filename,"w");echo "<table>";
    $postid=DBC::fetchcolumn("SELECT postid FROM equip WHERE EQNUM='{$filter}'",0);
    $level=1;
    get_children($postid,$fh);
    echo "</table>";fclose($fh);
?>
<p><?php echo $export_filename; ?> is finished</p>
<?php
    fclose($fh);
?>
