<?PHP
/**
* SysAdmin Module, will not be distributed!
* By switching databases we can test the existing configurations...
* 
* @author  Werner Huysmans 
* @access  public
* @package framework
* @subpackage framework
* @filesource
* CVS
* $Id: switch_DB.php,v 1.3 2013/11/04 07:38:18 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/switch_DB.php,v $
* $Log: switch_DB.php,v $
* Revision 5.0  2015/07/26 17:56  werner
* - Removed old code for CONFIG?
* - The script is accessed now from within the login Window, where an extra option permits to switch databases
* @todo check if a non-admin can do this?
*/
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php");

switch ($_REQUEST['STEP']) {
    case "DB": {
        $fname="../includes/config_settings.inc.php";
        $lines=file($fname);
        $out="";
        foreach($lines as $line) {
            $is_CMMS_DB=strpos($line,"define(\"CMMS_DB");
            if ($is_CMMS_DB===false) {
                $out .= $line;
            } else {
                $out .= "define(\"CMMS_DB\",\"{$_REQUEST['DATABASE_SWITCH']}\");\n";
            }
        }
        $handle=fopen($fname,"w");
        if ($handle) {
            fwrite($handle,$out);
        }
        fclose($handle);
?>
<script type=text/javascript>
function reload()
{    top.location = "../index.php"; 
} 
setTimeout("reload();", 500)
</script>
<?PHP
        break;
    }
    default: {
        require("setup.php");
/**
* Projects REMACLE, DEMB, AUBY and BALEN are incompatible with Version5
*/
        $databases=array("DEVELOPMENT","HOMENET","VPK");
        $tpl=new smarty_mycmms();
        $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("databases",$databases);
        $tpl->assign("settings",array(array(
            "SERVER_ADDRESS" => SERVER_ADDRESS,
            "MYCMMS_ROOTDIR" => MYCMMS_ROOTDIR,
            "DOC_PATH" => DOC_PATH,
            "CMMS_DB" => CMMS_DB,
            "WAMP_DIR" => WAMP_DIR,
            "MYCMMS_PEAR_PATH" => MYCMMS_PEAR_PATH,
            "SMARTY" => SMARTY,
            "PEAR_PATH" => PEAR_PATH,
            "CMMS_STYLESHEET" => CMMS_STYLESHEET,
            "CMMS_LIB" => CMMS_LIB)));
        $tpl->display_error("fw/switch_DB.tpl");        
        break;
    }
} // EO Switch
?>

