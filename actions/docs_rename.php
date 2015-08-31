<?PHP
/**
* Moving a file in the documentation
*  
* @author  Werner Huysmans 
* @access  public
* @package framework
* @filesource
*/
require("../includes/config_mycmms.inc.php");
require("class_inputPageSmarty.php");
/**
* class movePage 
* @package framework
*/
class movePage extends inputPageSmarty {
public function page_content() {
    $data=$this->get_data($this->input1,$this->input2);
    $DB=DBC::get();
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->debugging=false;
    $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign('data',$data);
    $tpl->display_error("tw/filedesc_edit.tpl");
} // EO page_content
function process_form() {
    $DB=DBC::get();
    try {
        $DB->beginTransaction();
        if ($_REQUEST['filedescription']!=$_REQUEST['filedescription_new']) {
            DBC::execute("UPDATE dd SET filedescription=:filedescription WHERE filename=:filename",
                array("filedescription"=>$_REQUEST['filedescription_new'],"filename"=>$_REQUEST['filename_new']));
/** Since this is a pure renaming, we must relink
            $basename=basename($_REQUEST['FILENAME_NEW']);
            DBC::execute("UPDATE document_links SET FILENAME=:filename_new,BASENAME=:basename WHERE FILENAME=:filename_old",
                array("filename_new"=>$_REQUEST['FILENAME_NEW'],"basename"=>$basename,"filename_old"=>$_REQUEST['FILENAME_OLD']));
        }
        DBC::execute("UPDATE document_descriptions SET FILEDESCRIPTION=:filedescription WHERE FILENAME=:filename_new",
            array("filedescription"=>$_REQUEST['FILEDESCRIPTION'],"filename_new"=>$_REQUEST['FILENAME_NEW']));
*/            
        }     
        $DB->commit();   
        return __FILE__." OK";
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    } // EO try
} // EO process_form
} // EO Class

$inputPage=new movePage();
$inputPage->data_sql="SELECT * FROM dd WHERE filename='{$_SESSION['filename']}'";
$inputPage->flow();
?>    