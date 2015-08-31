<?php
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
DBC::execute("CALL car_costs()",array());
header("location: ../_main/list.php?query_name=CIE_PROKMS");
?>
