<?php
/** search_record.php: Relay function to transmit criteria to query. Transmits the query with its parameters back to the list function.
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package framework
* @subpackage searching
* @filesource
* CVS
* $Id: search_record.php,v 1.3 2013/04/27 09:11:14 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/_main/search_record.php,v $
* $Log: search_record.php,v $
* Revision 1.3  2013/04/27 09:11:14  werner
* Minor change
*
* Revision 1.2  2013/04/17 05:34:55  werner
* Inserted CVS variables Id,Source and Log
*/
require("../includes/config_mycmms.inc.php");
$_SESSION['criteria']=$_REQUEST['ID']; 
$QUERY_NAME=$_REQUEST['QUERY_NAME'];
switch ($_SESSION['system']) {
case 'td':
case 'oee':
case 'production':
case 'pms':
case 'home':
    $link="list.php?query_name=".$QUERY_NAME;
    break;
default:
    $link="list.php?query_name=".$QUERY_NAME;
    break; 
} // End of switch
?>
<html>
<script type="text/javascript">
<!--
function show_results()
{	top.maintmain.location='<?PHP echo $link; ?>';
}
//-->
setTimeout("show_results();", 500);
</script>
<body>
</body>
</html>
