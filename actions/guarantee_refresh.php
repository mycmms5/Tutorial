<?PHP
/**
 * @version $Id: guarantee_refresh.php,v 1.1.1.1 2013/04/21 07:27:28 werner Exp $
 * @copyright 2004 
 **/
require("../includes/config_mycmms.inc.php");
$DB=DBC::get();
DBC::execute("CALL GUARANTEE_REFRESH()",array());

# Create Text file 
$result=$DB->query("SELECT Year(PurchaseDate) AS PYR,Item FROM Guarantee WHERE Price > 75 ORDER BY PurchaseDate");
$TextFile = "../documents/export/guarantee_details.txt";
if ($myFile = fopen($TextFile,"w+")) 
{	foreach($result->fetchAll(PDO::FETCH_OBJ) as $row) {   
        fwrite($myFile,$row->PYR.":".$row->Item.chr(13));
    }
}
fclose($myFile);

$data=$DB->query("SELECT GT.Jaar AS 'Jaar', AUDIO AS 'Audio', VIDEO AS 'Video', PC AS 'PC', Software AS 'Software', Office AS 'Office', Home AS 'Home', Abo AS 'Abo',Cie AS 'Cie',AUDIO+VIDEO+PC+Software+Office+Abo+Home+Cie AS 'Total', Remarks AS 'text'  FROM Guarantee_tmp GT LEFT JOIN Guarantee_remarks GR ON GT.Jaar=GR.Jaar ORDER BY GT.Jaar DESC",PDO::FETCH_ASSOC);

require("setup.php");
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->debugging=false;
$tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
$tpl->assign("data",$data);
$tpl->display("action/guarantee_refresh.tpl");
?>


