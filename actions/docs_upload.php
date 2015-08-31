<?php
/** 
* Uploading files 
* @author  Werner Huysmans
* @access  public
* @package framework
* @filesource
* CVS
* $Id: docs_upload.php,v 1.1 2013/12/25 08:50:00 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/docs_upload.php,v $
* $Log: docs_upload.php,v $
* Revision 1.1  2013/12/25 08:50:00  werner
* Synced versions with myCMMS
*
* Revision 1.3  2013/11/04 07:46:51  werner
* CVS version shows
*
* Revision 1.2  2013/11/04 07:43:51  werner
* CVS version shows
*
* Revision 1.1  2013/11/04 07:40:46  werner
* Replaces linking_documents and tab_upload
*
* Revision 1.2  2013/06/08 11:41:37  werner
* Minor
*
* Revision 1.1  2013/04/30 12:46:13  werner
* Usable in all directories, the link parameters allows to use a type of document
*
* Revision 1.1  2013/04/27 09:04:16  werner
* Uploading files to mediawiki tree
*
* Revision 1.2  2013/04/17 05:59:41  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");
$version=__FILE__." :V5.0 Build 20150808";
unset($_SESSION['PDO_ERROR']);
define("UPLOAD_DIR","../documents/upload/");    # /common/documents_DEMO/upload/
define("UPLOAD_TMP","../documents/tmp/");     # /common/documents_DEMO/upload/tmp/
/**
* Is this a valid upload?
* 
* @param mixed $code
*/
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

require("setup.php");
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->debugging=false;
$tpl->assign('version',$version);
$tpl->assign("stylesheet",STYLE_PATH."docs_upload.css");

switch ($_REQUEST['STEP']) {
case "1": {
    $DB=DBC::get();
    $errors=array();
/**
* Check the uploaded file and move it to the /uploads/tmp directory    
*/
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
    $result=$DB->query("SELECT filename,md5 FROM dd WHERE filename='{$file_info['name']}'");
    $file_data=$result->fetch(PDO::FETCH_ASSOC);
    $filename=$file_data['filename']; $md5=$file_data['md5'];
    $hash=md5_file($file_info['tmp_name']);
    $sha1=sha1($file_info['name']);
    if (!move_uploaded_file($file_info['tmp_name'],UPLOAD_TMP.$file_info['name'])) {
        throw new Exception("File ".$file_info['tmp_name']." could not be moved to ".UPLOAD_TMP);
    } 
/**
* Smarty
* Show errors when available
* Show INSERT when file is NEW
* Show UPDATE when file exists    
*/
    
    $tpl->assign("step","DBCHECK");
    $tpl->assign('version',$version);
    if (count($errors)==0) {
        $error_count=false;
    } else {
        $error_count=true;
    }
    $tpl->assign("error_count",$error_count);
    $tpl->assign("errors",$errors);
    $tpl->assign("file_info",$file_info);
    if (empty($filename)) {
        $file_exists=false;
    } else {
        $file_exists=true;
        if ($hash != $md5) {
            $file_different=true;   
        } else {
            $file_different=false;
        }
    }
    $result=$DB->query("SELECT dl.*,dd.filedescription,dd.md5 FROM dl LEFT JOIN dd ON dl.filename=dd.filename WHERE dl.filename='{$file_info['name']}'");
    $data=$result->fetch(PDO::FETCH_ASSOC);
    $tpl->assign("file_exists",$file_exists);
    $tpl->assign("data",$data);
    $tpl->assign("md5",$hash);
    $tpl->assign("sha1",$sha1);
    $tpl->assign("store_path",$stored."/".$file_info['name']);
    $tpl->assign("step","DBCHECK");
    break;
} // EO Step 1
case "2": {
    $DB=DBC::get();
    try {
        $DB->beginTransaction();
        switch ($_REQUEST['ACTION']) {
            case "UPDATE":
            DBC::execute("INSERT INTO dl (ID,filename,type,link) VALUES (NULL,:filename,:type,:link)",
                array("filename"=>$_REQUEST['filename'],
                    "type"=>$_SESSION['link'],
                    "link"=>$_SESSION['Ident_1']));
            $upload_errors=array("exists"=>true,"mkdir"=>true,"move"=>true);
            break;
            case "LINK":
            DBC::execute("INSERT INTO dl (ID,filename,type,link) VALUES (NULL,:filename,:type,:link)",
                array("filename"=>$_REQUEST['filename'],
                    "type"=>$_SESSION['link'],
                    "link"=>$_SESSION['Ident_1']));
            $upload_errors=array("exists"=>true,"mkdir"=>true,"move"=>true);
            break;
            case "INSERT":
            DBC::execute("INSERT INTO dd (filename,filedescription,sha1,md5) VALUES (:filename,:filedescription,:sha1,:md5)",
                array("filename"=>$_REQUEST['filename'],
                    "filedescription"=>$_REQUEST['FILE_DESCRIPTION'],
                    "sha1"=>$_REQUEST['sha1'],
                    "md5"=>$_REQUEST['md5']));
            DBC::execute("INSERT INTO dl (ID,filename,type,link) VALUES (NULL,:filename,:type,:link)",
                array("filename"=>$_REQUEST['filename'],
                    "type"=>$_SESSION['link'],
                    "link"=>$_SESSION['Ident_1']));
            $stored=substr($_REQUEST['sha1'],0,2)."/".substr($_REQUEST['sha1'],0,1);
            $upload_errors=array();
            $upload_errors['mkdir']=mkdir(UPLOAD_DIR.$stored,NULL,TRUE);
            $upload_errors['move']=rename(UPLOAD_TMP.$_REQUEST['filename'],UPLOAD_DIR.$stored."/".$_REQUEST['filename']);
            $upload_errors['exists']=file_exists(UPLOAD_DIR.$stored."/".$_REQUEST['filename']);
            $upload_errors['upload_path']=UPLOAD_DIR.$stored."/".$_REQUEST['filename'];
            $upload_errors['upload_tmp']=UPLOAD_TMP;
            break;                            
        }
        $DB->commit();
    } catch (Exception $e) {
        $DB->rollBack();
        PDO_log($e);
    }
    $DB=DBC::get();
    $sql="SELECT dl.*,CONCAT(LEFT(sha1,2),'/',LEFT(sha1,1),'/') AS 'filepath',md5,filedescription FROM dl LEFT JOIN dd ON dl.filename=dd.filename WHERE link='{$_SESSION['Ident_1']}' AND type='{$_SESSION['link']}' AND dd.md5 <> '0'";
    $result=$DB->query($sql);
    $uploads=$result->fetchAll(PDO::FETCH_ASSOC);
    
    $tpl->assign("step","END");
    $tpl->assign("object",$_SESSION['Ident_1']);
    $tpl->assign("upload_dir",UPLOAD_DIR);
    $tpl->assign("uploads",$uploads);
    $tpl->assign("upload_errors",$upload_errors);
    break;
} // EO Step 2
default: {
    $_SESSION['link']=$_REQUEST['link'];
    $DB=DBC::get();
    $sql="SELECT dl.*,CONCAT(LEFT(sha1,2),'/',LEFT(sha1,1),'/') AS 'filepath',md5,filedescription FROM dl LEFT JOIN dd ON dl.filename=dd.filename WHERE link='{$_SESSION['Ident_1']}' AND type='{$_REQUEST['link']}' AND dd.md5 <> '0'";
    $result=$DB->query($sql);
    $uploads=$result->fetchAll(PDO::FETCH_ASSOC);

    $tpl->assign("step","FORM");
    $tpl->assign("object",$_SESSION['Ident_1']);
    $tpl->assign("upload_dir",UPLOAD_DIR);
    $tpl->assign("uploads",$uploads);
    } // EO default
} // EO switch

$tpl->display_error("action/docs_upload.tpl");

?>
