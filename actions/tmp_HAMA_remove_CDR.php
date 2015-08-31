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
    padding: 1px;
}
td.noHAMA {
    color: black;
    background-color: orange; 
    font-size: 12px; }
</style>
<?php
/**
* Action: Remove the CDR from the HAMA lists (records2 table)
* @package tabwindow
* @subpackage music
* @var mixed
*/  
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
$i=1;
/**
* Step1: Select all converted records (STORAGE is NOT NULL) and run through them.
* Use the RecordingID to get the corresponding HAMA record and DELETE it.
*/
$result_CDR=$DB->query("SELECT RecordingID,Artist,Title,Format,Classification,STORAGE FROM records WHERE Format LIKE 'CDR%' AND STORAGE IN ('I_','_V','IV') ORDER BY Artist");
$records_CDR=$result_CDR->fetchAll(PDO::FETCH_ASSOC);
echo "<table>";
foreach ($records_CDR as $record)
{   echo "<tr><td>$i</td><td>".$record['Artist']."</td><td>".$record['Title']."</td><td>".$record['Format']."</td><td>".$record['RecordingID']."</td><td>".$record['STORAGE']."</td>";
    $hama=DBC::fetchColumn("SELECT COUNT(*) FROM records2 WHERE RecordingID={$record['RecordingID']}",0);
    if ($hama==0) {
        echo ("<td class='noHAMA'>No HAMA storage</td></tr>");
    } else {
        try {
            $DB->beginTransaction();
            DBC::execute("DELETE FROM records2 WHERE RecordingID={$record['RecordingID']}");
            $DB->commit();
            echo "</tr>";
        } catch (Exception $e) {
            $DB->rollBack();
        }
    }
    $i++;
}
echo "</table>";
?>
