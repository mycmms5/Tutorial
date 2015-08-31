<?php
/** 
* Uploading files 
* @author  Werner Huysmans
* @access  public
* @package BETA
* @subpackage upload
* @filesource
* CVS
* $Id: upload.php,v 1.2 2013/04/17 05:59:41 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/upload.php,v $
* $Log: upload.php,v $
* Revision 1.2  2013/04/17 05:59:41  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");

function assertValidUpload($code) {
    if($code==UPLOAD_ERR_OK) { return; }
    switch($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $msg='Image is too large';
            break;
        case UPLOAD_ERR_PARTIAL:
            $msg='Image was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $msg='No Image was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $msg='Upload folder not found';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $msg='Unable to write uploaded file';
            break;
        case UPLOAD_ERR_EXTENSION:
            $msg='Upload failed due to extension';
            break;
        default:
            $msg='Unknown error';           
    }
    throw new Exception($msg);
} // EO assertValidUpload 

switch ($_REQUEST['STEP']) {
case "1": {
    $errors=array();
    try {
        if (!array_key_exists('UPLOAD',$_FILES)) {
            throw new Exception('File-info not found in uploaded data');
        }
        $file_info=$_FILES['UPLOAD'];
        assertValidUpload($file_info['error']); 
        if (!is_uploaded_file($file_info['tmp_name'])) {
            throw new Exception('File is not an uploaded file');
        } 
        $info=getimagesize($file_info['tmp_name']);
        if (!$info) {
        // throw new Exception('File is not an image');
            $info=10000;
        }
    } catch (Exception $ex) {
        $errors[]=$ex->getMessage();
    }
    if (count($errors)==0) {
/** No problems with upload, we start the actions
*/
        $hash=md5_file($file_info['tmp_name']);
        $sha1=sha1($file_info['name']);
        $stored=substr($sha1,0,2)."/".substr($sha1,0,1);
        $mkdir_OK=mkdir("../documents/upload/".$stored,NULL,TRUE);
        $upload_OK=move_uploaded_file($file_info['tmp_name'],"../documents/upload/".$stored."/".$file_info['name']);
        require("setup.php");
        $tpl=new smarty_mycmms();
        $tpl->caching=false;
        $tpl->debugging=false;
        $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("md5",$hash);
        $tpl->assign("sha1",$sha1);
        $tpl->assign("store_path",$stored."/".$file_info['name']);
        $tpl->assign("mkdir_OK",$mkdir_OK);
        $tpl->assign("upload_OK",$upload_OK);
        $tpl->display_error("action/upload_storage_point.tpl");
    } else {
        require("setup.php");
        $tpl=new smarty_mycmms();
        $tpl->caching=false;
        $tpl->debugging=false;
        $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("errors",$errors);
        $tpl->display_error("action/upload_errors.tpl");
    }
    break;
}
default: {
    $DB=DBC::get();
            
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->debugging=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->display_error("action/upload_form.tpl");
}
}
?>
