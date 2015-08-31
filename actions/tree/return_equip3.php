<html>
<head>
<?PHP
/** 
* @author  Werner Huysmans 
* @access  public
* @package framework
* @subpackage tree
* @filesource
*/
$ini_array = parse_ini_file("treewindow.ini", true);
$treeform=$ini_array['EQUIP3']["treeform"];
$ID=$ini_array['EQUIP3']["ID"];
$ID_DESC=$ini_array['EQUIP3']["ID_DESC"];
?>
<script>
opener.document.<?PHP echo $treeform.".".$ID.".value='".$_REQUEST['id1']."'"; ?>;
opener.document.<?PHP echo $treeform.".".$ID_DESC.".value='".$_REQUEST['id2']."'"; ?>;
window.close();
</script>
</head>
</html> 