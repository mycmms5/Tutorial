<?php
/** 
* Line Graph -- Reliability data
* 
* @author  Werner Huysmans 
* @access  public
* @package JPGRAPH
* @subpackage TRENDS
* @filesource
*/
if (!isset($_REQUEST['STEP1'])) {
$nosecurity_check=true;    
require("../includes/config_mycmms.inc.php");    
$LINES=array("M019-02-02-50","M019-02-02-51","M019-02-02-52","M019-02-02-53","M019-02-02-54","M019-02-02-55");    
?>    
<html>
<body>
<h2><?PHP echo _("Select Production Line"); ?></h2>
<form action="<?PHP echo $PHP_SELF; ?>" name="graph">
<table width="600">
<tr><td><?PHP echo _("Select Production line"); ?></td><td><?PHP echo create_combofix("FILTER",$LINES,"",""); ?></td></tr>
<tr><td colspan="2" align="left"><input type="submit" name="STEP1" value="<?PHP echo _("Select Production line"); ?>"></td></tr>
</table>
</form>
</body>
</html>
<?PHP
} else {
require ("config.inc.php");
require ("myCMMS/class_jpgraph_linechart.php");
require_once("myCMMS/class_PDO_MySQL.php");
$DB=DBC::get();
/** Data
* The X-labels are the registered periods. At this time there MUST be a value for every period
* @var JPG_LINECHART
*/
$result=$DB->query("SELECT DISTINCT PERIOD FROM equip_downtime WHERE PERIOD > '200900'");
if ($result) {
    $j=0;
    $xdata=array();
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $xdata[$j]=$row["PERIOD"];
        $j++;
    }
}
/** Data
* The Legend and the Y-data 
* @var mixed
*/
$filter=$_REQUEST['FILTER'];
$equips=array();
$result=$DB->query("SELECT DISTINCT FUNCTION FROM equip_downtime WHERE EQNUM='$filter' AND PERIOD>'200900'");
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $equips[]=$row['FUNCTION'];
}
$equips_num=count($equips);
for ($i=0; $i<$equips_num;$i++) {
    $result=$DB->query("SELECT DOWNTIME FROM equip_downtime WHERE EQNUM='$filter' AND FUNCTION='{$equips[$i]}'");
    if ($result) {
        $j=0;
        $ydata[$i]=array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row["DOWNTIME"]==-1) {
                $ydata[$i][$j]="-";
            } else {
                $ydata[$i][$j]=$row["DOWNTIME"];    
            }
            $j++;
        }
    }
}
/**
* Drawing the LineChart
* 
* @var JPG_LINECHART
*/
$linechart=new JPG_LINECHART();
$linechart->linechart_title="Reliability data for ".$filter;
$linechart->init(900,600);
$linechart->set_X_scale($xdata);
for ($i=0; $i<$equips_num; $i++) {
    $linechart->add_Y_line($i,$ydata[$i],$equips[$i]);
}
$linechart->show_LINECHART();
}
?>

