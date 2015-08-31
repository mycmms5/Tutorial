<?PHP
/**
 * @version $Id: calculate_project_cost.php,v 1.1.1.1 2013/04/21 07:27:28 werner Exp $
 * @copyright 2004 
 **/
$stylesheet="CSS_INPUT";
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
DBC::execute("CALL PROJECTCOST_REFRESH()",array());
?>
<script type="text/javascript">
function reload() {    
    window.location="../_main/list.php?query_name=WO_DEFAULT"; 
} 
setTimeout("reload();", 250);
</script>