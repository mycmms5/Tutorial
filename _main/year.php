<?php
/**
* @author Werner Huysmans
* @version 1.0
* @package webcalendar
* @subpackage views
* @filesource
* CVS
* $Id
* $Source
* $Log
*/
require_once '../includes/functions_mycmms.php';   # DB connection
require_once 'functions_webcal.php';   # WebCalendar functions
require_once 'setup.php';
$user=$_SESSION['webcal_login'];
$year=$_REQUEST['year'];   // Sets the date
if (empty($year)) $year=date('Y');

$thisyear = $year;
$nextYear = $year + 1;
$prevYear = $year - ( $year > '1903' ? 1 : 0 );
$startdate = mktime ( 0, 0, 0, 1, 1, $year );
$enddate = mktime ( 23, 59, 59, 12, 31, $year );
# make minicals
$month=0;
for($row=1; $row <= 3; $row++) {
    for($col=1; $col <= 4; $col++, $month++ ) {
        $minicals[$row][$col]=display_small_month($month,$year,false );
    }
}
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->debugging=false;
$tpl->assign("view_name","myCMMS_Year_View");
$tpl->assign("prevYear",$prevYear); // Previous Year
$tpl->assign("nextYear",$nextYear); // Next Year
$tpl->assign("thisYear",$year);
$tpl->assign("class",$class);
$tpl->assign("minicals",$minicals);
$tpl->display_error("year.tpl");
?>


