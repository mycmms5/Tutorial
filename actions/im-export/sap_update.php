<?php
/**
* Updating equipment table with new SAP numerotation
* 
* @author  Werner Huysmans 
* @access  public
* @package BETA
* @subpackage archiving
* @filesource
*/
require("../includes/config_mycmms.inc.php");
require("setup.php");
$_SESSION['PDO_ERROR']="";
$DB=DBC::get();

#DBC::execute("DELETE FROM equip_link",array());
echo("Deleted all ancient records from equip_link</br>");
#DBC::execute("INSERT INTO equip_link (EQNUM) SELECT EQNUM FROM equip",array());
echo("Copied all existing records from equip</br>");
#DBC::execute("UPDATE equip_link SET SAP=CONCAT('S103-',SUBSTR(EQNUM,6))",array());
echo("- Change all equip.EQNUM=M019 into S103</br>");
# Move all deleted
#DBC::execute("UPDATE equip_link el LEFT JOIN equip_sap es ON el.EQNUM=es.EQNUM SET el.SAP_ROOT=es.EQROOT WHERE es.TODO='DEL'",array());
echo("- Move all deleted to S103-DELETED</br>");
# Change all M019 to S103
#DBC::execute("UPDATE equip_sap SET EQNUM=CONCAT('S103-',SUBSTR(EQNUM,6)),EQROOT=CONCAT('S103-',SUBSTR(EQROOT,6)) WHERE TODO='NEW'",array());
#DBC::execute("UPDATE equip_sap SET EQNUM=CONCAT('S103-',SUBSTR(EQNUM,6)),EQROOT=CONCAT('S103-',SUBSTR(EQROOT,6)) WHERE TODO='EDIT'",array());
#DBC::execute("UPDATE equip_sap SET EQNUM=CONCAT('S103-',SUBSTR(EQNUM,6)),EQROOT=CONCAT('S103-',SUBSTR(EQROOT,6)) WHERE TODO='SPARE'",array());
#echo("- Renamed all equip_sap M019 to S103</br>");
$records_edited=0; $errors_edited=0;
echo("<ul>");
$result=$DB->query("SELECT * FROM equip_sap WHERE TODO='EDIT'");
foreach($result->fetchAll(PDO::FETCH_ASSOC) AS $record) {
    $EQNUM=DBC::fetchcolumn("SELECT EQNUM FROM equip WHERE EQNUM LIKE '%{$record['EQNUM_OLD']}%'");
    if (empty($EQNUM)) {
        echo("<li>No corresponding EQNUM found for {$record['EQNUM_OLD']}</li>");
        $errors_edited++;
    } else {
        # echo("<li>$EQNUM - {$record['EQNUM']}-{$record['EQ_DESC']} / BOM: {$record['SPARECODE']}</li>");        
        $records_edited++;
    }   
}
echo("</ul>EDIT finished (OK: $records_edited)</br>Not found $errors_edited</br>");

echo("<ul>");
$spares_added=0; $errors_spares=0;
$result=$DB->query("SELECT * FROM equip_sap WHERE TODO='SPARE'");
foreach($result->fetchAll(PDO::FETCH_ASSOC) AS $record) {
    $EQNUM=DBC::fetchcolumn("SELECT EQNUM FROM equip WHERE EQNUM='{$record['EQNUM']}'");
    if (empty($EQNUM)) {
        echo("<li>No corresponding EQNUM found for {$record['EQNUM']}</li>");
        $errors_spares++;
    } else {
        # echo("<li>$EQNUM - {$record['EQNUM']}-{$record['EQ_DESC']} / BOM: {$record['SPARECODE']}</li>");        
        $spares_added++;
    }   
}
echo("</ul>SPARE finished (OK: $spares_added)</br>Not found $errors_spares</br>");


echo("SAP Update terminated</br>");
?>


