<?PHP
/**
* Utility to show OEE timeline
* 
* @author  Werner Huysmans 
* @access  public
* @package JPGRAPH
* @subpackage TRENDS
* @filesource
*/
session_cache_limiter("nocache");
require("../includes/config_mycmms.inc.php");
require('HTML/Table.php');
?>
<style type="text/css">
<?PHP require(CSS_INPUT); ?>
</style>
<?PHP
require(CMMS_STYLESHEET."/calendar-win2K-1.css");
$lines=array("CRH-FLO-BETON2");
$DB=DBC::get();
?>
<html> 
<head> 
<meta http-equiv="Content-Language" content="en-us"> 
<meta http-equiv="refresh" content="500">
<title>TimeLine (BarGraph) .. by Werner Huysmans</title> 
<script src="../libraries/calendar.js" type="text/javascript"></script>
<script src="../libraries/calendar-en.js" type="text/javascript"></script>
<script src="../libraries/calendar-setup.js" type="text/javascript"></script>
</head> 
<body> 
<form>
<table>
<tr><td><?PHP echo _("OEE TimeLine starts at:"); ?></td><td><?PHP echo create_time_box("START","start",10,$_REQUEST['START']); ?></td></tr>
<tr><td><?PHP echo _("OEE TimeLine ends at:"); ?></td><td><?PHP echo create_time_box("END","end",10,$_REQUEST['END']); ?></td></tr>
<tr><td><?PHP echo _("Production Line:"); ?></td><td><?PHP echo create_combofix("LINE",$lines,$_REQUEST['LINE'],$_REQUEST['LINE']); ?></td></tr>
<tr><td colspan="2"><input type="submit" class="submit" value="<? echo _("Show OEE Graph"); ?>" name="close"></td></tr>
</table>
</form>
<?php 
/** Only show when Input was given
* 
*/
if (isset($_REQUEST['START'])) {
/**
*   Calculating scales    
*/
    $start=strtotime($_REQUEST['START']);
    $span=strtotime($_REQUEST['END'])-strtotime($_REQUEST['START']);
    $end=strtotime($_REQUEST['END']);
    echo "TimeSpan= ".$span."<BR>";
    echo "StartOfGraph= ".$start."<BR>";
    echo "EndOfGraph= ".$end."<BR>";
    $ImageWidth=1200;
    $ImageHeight=250;
    $ScaleHeight=$ImageHeight-50;
    $BottomGraph=$ImageHeight-60;
    $factor=$ImageWidth/$span;
    echo "Factor= ".$factor."<HR>";
/** Reading the relevant data from the OEE database
* put your comment there...
* @var mixed
*/
    $data=array();
    $data_element=array();
    $sql="SELECT START,END,OEE FROM events WHERE EQNUM='{$_REQUEST['LINE']}' AND START>'{$_REQUEST['START']}' AND END<'{$_REQUEST['END']}' AND STATUS1=1";
    $result=$DB->query($sql);
    if ($result) {
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $oee) {
            $data_element['START']=(strtotime($oee['START'])-$start)*$factor;
            $data_element['END']=(strtotime($oee['END'])-$start)*$factor;
            $data_element['OEE']=$oee['OEE'];
            $data[]=$data_element;
        } // foreach
    }
//    print_r($data);
/** Presets for the Image
* Logic width is 1200 x 600
* 
* @var mixed
*/

    $im = imagecreate($ImageWidth,$ImageHeight); // width , height px 
    $white = imagecolorallocate($im,255,255,255); // allocate some color from RGB components remeber Physics 
    $black = imagecolorallocate($im,0,0,0);   // 
    $red = imagecolorallocate($im,255,0,0);   // 
    $green = imagecolorallocate($im,0,255,0); // 
    $blue = imagecolorallocate($im,0,0,255);  // 
    // BarGraph 
    $y_0=50;
    $y_height=$BottomGraph;
    imagefilledrectangle($im,0,$y_0,$ImageWidth,$y_height,$green);
    // get into some meat now, cheese for vegetarians; 
    foreach($data as $period) {
        switch($period['OEE']) {
            case 'RUN': $color=$green; break;
            case 'STOP': $color=$red; break;
            case 'TECH': $color=$blue; break;
            default: $color=$blue; break;
        }
        settype($period['START'],"integer");
        settype($period['END'],"integer");
        imagefilledrectangle($im,$period['START'],$y_0,$period['END'],$y_height,$color);
    } 
/** showing Titles
*   imagestring(resource image,font,x,y,string,color)
*/

    imagestring($im,5,$ImageWidth/2,5,$_REQUEST['LINE'],$black); 
    imagestring($im,5,$ImageWidth/2,20,"OEE data by Werner Huysmans",$red); 
    imagestring($im,5,0,$ScaleHeight,$_REQUEST['START'],$black);
    imagestring($im,5,$ImageWidth-150,$ScaleHeight,$_REQUEST['END'],$black); 

    imagejpeg( $im, "graph.jpeg",90); 
    imagedestroy($im); 
    echo "<img src='graph.jpeg' name='refresh'><p></p>"; 
?>
<script language="JavaScript" type="text/javascript"> 
    var t = 120 // interval in seconds 
    image = "graph.jpeg" //name of the image 
    function Start() { 
        tmp=new Date(); 
        tmp="?"+tmp.getTime() 
        document.images["refresh"].src=image+tmp 
        setTimeout("Start()", t*1000) 
    } 
    Start(); 
</script> 
<?PHP
    $result=$DB->query($sql);
    if ($result) {
        $tbl_events=new HTML_Table();
        $header=array("Start","End","OEE");
        $tbl_events->addRow($header,'','TH');
        foreach($result->fetchAll(PDO::FETCH_NUM) as $row) {
            $tbl_events->addRow($row);
        } // foreach
        for ($i=0;$i < $tbl_events->getRowCount(); $i++) {
            if ($i%2) {    
                $tbl_events->setRowAttributes($i,array("BgColor"=>"#C0FFC0"),true);
            }
        }
        echo $tbl_events->toHtml();
    }
} // Isset?
?> 
</body> 
</html
?>
