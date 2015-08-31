<html>
<head>
<?PHP
/** 
* Return the EQNUM/DESCRIPTION back to calling window
* 
* @author  Werner Huysmans 
* @access  public
* @package framework
* @subpackage tree
* @filesource
*/
$ini_array = parse_ini_file("treewindow.ini", true);
$treeform=$ini_array['OEE']["treeform"];
$ID=$ini_array['OEE']["ID"];
$ID_DESC=$ini_array['OEE']["ID_DESC"];
?>
<script>
opener.document.<?PHP echo $treeform.".".$ID.".value='".$_REQUEST['id1']."'"; ?>;
opener.document.<?PHP echo $treeform.".".$ID_DESC.".value='".$_REQUEST['id2']."'"; ?>;
window.close();
</script>
</head>
</html> 
