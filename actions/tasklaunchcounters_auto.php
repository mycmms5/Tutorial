<?php
/** 
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @changed:   20081201
* @access  public
* @package ppm
* @subpackage tasklaunch
* @filesource
* CVS
* $Id: tasklaunchcounters_auto.php,v 1.2 2013/04/17 05:59:41 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/tasklaunchcounters_auto.php,v $
* $Log: tasklaunchcounters_auto.php,v $
* Revision 1.2  2013/04/17 05:59:41  werner
* Inserted CVS variables Id,Source and Log
*
*/
set_time_limit(0);  // No time limit...
require("../includes/config_mycmms.inc.php");  
require("HTML/Table.php");
require(CMMS_LIB."/class_actionPage.php");

$actionPage=new actionPage();
$actionPage->stylesheet=CSS_INPUT;
$actionPage->HeaderPage(_("Automatic Task launching for counters Sub-System"));
$fh=fopen(LAUNCH_LOG,"a+");
$DB=DBC::get();
/** Sequence is straightforward:
* - Make sure all counters are refreshed 
* - Launch all due tasks that are active
* @var mixed
*/
$DB=DBC::get();
try {
    $DB->beginTransaction();
    DBC::execute("DELETE FROM counter",array());    // Erase old states
    DBC::execute("INSERT INTO counter (COUNTER,VALUE) SELECT INDICATOR,MAX(VALUE) FROM wo_checks wc LEFT JOIN taskeq te ON wc.INDICATOR=te.COUNTER WHERE te.COUNTER IS NOT NULL GROUP BY INDICATOR",array());   // Retrieve the latest counters
    DBC::execute("UPDATE taskeq te INNER JOIN counter c ON te.COUNTER=c.COUNTER SET te.STATE=c.VALUE WHERE te.COUNTER IS NOT NULL",array());    
    $DB->commit();
} catch (Exception $e) {
    $DB->rollBack();
    PDO_log($e);
}
$result=$DB->query("SELECT task.TASKNUM AS 'TASKNUM',EQNUM AS 'EQNUM' FROM task LEFT JOIN taskeq ON task.TASKNUM=taskeq.TASKNUM WHERE ACTIVE=1 AND STATE>LASTCOUNTER+NUMOFDATE AND taskeq.WONUM IS NULL");
$actionPage->table_sql="SELECT TASKNUM,EQNUM FROM taskeq WHERE ACTIVE=1 AND STATE>LASTCOUNTER+NUMOFDATE AND taskeq.WONUM IS NULL";
$actionPage->table_header=array("TASKNUM","EQNUM");
$actionPage->show_table();
fwrite($fh,"Log AutoLauncher:".now2string(true)."\n");

$i=0;
if ($result) {
    foreach($result->fetchAll(PDO::FETCH_OBJ) as $row) {
        try{
            $DB->beginTransaction();
            $pTASKNUM=$row->TASKNUM; $pEQNUM=$row->EQNUM;
            require("TXID_TASK2WO.php");
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
        fwrite($fh,$i.":".$row->TASKNUM." for machine ".$row->EQNUM." into ".$new_wo."\n");
        echo "<p>$i : {$row->TASKNUM} for machine {$row->EQNUM} into {$new_wo}</p>";
        $i++;
    }
} else {
    fwrite($fh,"No new tasks found");
}
fwrite($fh,"Found ".$i." tasks to launch\n");
fclose($fh);
if (false) {
    $fh=fopen(LAUNCH_LOG,"r");
    $content=fread($fh,filesize(LAUNCH_LOG));
    echo "<pre>".$content."</pre>";
}  
$actionPage->FooterPage();
set_time_limit(30);
?>

