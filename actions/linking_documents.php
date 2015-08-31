<?php
/**
* Linking Documents using PHPVC filesystem
* 
* @author  Werner Huysmans 
* @access  public
* @version $Id: linking_documents.php,v 1.6 2013/11/04 07:47:19 werner Exp $4.0
* @filesource
* @package framework
* @subpackage linking_docs
* @filesource
* CVS
* $Id: linking_documents.php,v 1.6 2013/11/04 07:47:19 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/actions/linking_documents.php,v $
* $Log: linking_documents.php,v $
* Revision 1.6  2013/11/04 07:47:19  werner
* CVS version shows
*
* Revision 1.5  2013/09/27 07:08:18  werner
* Integrating File Description
*
* Revision 1.4  2013/04/27 09:12:19  werner
* Debug correction: call documents() causes problem
*
* Revision 1.3  2013/04/17 05:44:53  werner
* Inserted CVS variables Id,Source and Log
*
*/
require("../includes/config_mycmms.inc.php");
require("setup.php");
unset($_SESSION['PDO_ERROR']);  // Reset previous errors

/** 
* Set the documents directory where we will search documents
* Standard ../../common/documents_DEMO where demo is the DB name
*/
define('PROJECT_DIR','../../common/documents_'.CMMS_DB);
function getFileType($filename) {
    $ext = substr(strrchr($filename, "."), 1);
    return file_exists('img/'.$ext.'.gif') ? $ext.'.gif' : 'txt.gif';
}
function translatePath($path) {
    if(substr($path, strlen($path)-2, 2) == '//')
        $path = substr($path, 0, strlen($path)-2);
    if(substr($path, strlen($path)-1, 1) == '/')
        $path = substr($path, 0, strlen($path)-1);
    
        if(substr($path, strlen($path)-2, 2) == '..')
        {
            $path = substr($path, 0, (strlen($path)-3));
            $path = substr($path, 0, strrpos($path, '/'));
        }
    return $path;
} // EO translatePath
function createLink($file,$docu_system) {
    switch($_SESSION['DOCTYPE']) {
    case 'WONUM':
        $sql_unique="SELECT COUNT(*) FROM document_links WHERE WONUM='{$_SESSION['Ident_1']}' AND FILENAME='{$file}'";
        $sql_insert="INSERT INTO document_links (WONUM,FILENAME,BASENAME) VALUES (:type,:filename,:basename)";
        break;
    case 'EQNUM':
        $sql_unique="SELECT COUNT(*) AS 'QTY' FROM document_links WHERE EQNUM='{$_SESSION['Ident_1']}' AND FILENAME='{$file}'";
        $sql_insert="INSERT INTO document_links (EQNUM,FILENAME,BASENAME) VALUES (:type,:filename,:basename)";
        break;
    case 'TASKNUM':
        $sql_unique="SELECT COUNT(*) AS 'QTY' FROM document_links WHERE TASKNUM='{$_SESSION['Ident_1']}' AND FILENAME='{$file}'";
        $sql_insert="INSERT INTO document_links (TASKNUM,FILENAME,BASENAME) VALUES (:type,:filename,:basename)";
        break;
    case 'ITEMNUM':
        $sql_unique="SELECT COUNT(*) FROM document_links WHERE ITEMNUM='{$_SESSION['Ident_1']}' AND FILENAME='{$file}'";
        $sql_insert="INSERT INTO document_links (FILENAME,BASENAME,WONUM,ITEMNUM,EQNUM,TASKNUM) VALUES (:filename,:basename,0,:type,'x','x')";
        break;
    }
    // Sanity check - do not add twice the same file...
    $exists=DBC::fetchcolumn($sql_unique,0);
    if ($exists!=0) { 
        exit(); 
    } else {
        $DB=DBC::get();
        $basename=basename($file);
        try {
            $DB->beginTransaction();
            DBC::execute($sql_insert,array("type"=>$_SESSION['Ident_1'],"filename"=>mysql_escape_string($file),"basename"=>$basename));
            DBC::execute("CALL documents()",array());
            $DB->commit();
        } catch (Exception $e) {
            $DB->rollBack();
            PDO_log($e);
        }
    }
};

/**
* In function of the document type, we will save the data differently
* Options:
* - WONUM 
* - EQNUM
* - TASKNUM
* - ITEMNUM
*/
if (isset($_REQUEST['DOCTYPE'])) {  $_SESSION['DOCTYPE']=$_REQUEST['DOCTYPE']; }
switch($_SESSION['DOCTYPE']) {
    case 'WONUM':
        $sql_select="SELECT FILENAME,FILEDESCRIPTION FROM vw_documents WHERE WONUM='{$_SESSION['Ident_1']}'";
        break;
    case 'EQNUM':
        $sql_select="SELECT dl.FILENAME, dd.FILEDESCRIPTION FROM document_links dl LEFT JOIN document_descriptions dd ON dl.FILENAME=dd.FILENAME WHERE EQNUM='{$_SESSION['Ident_1']}'";
        break;
    case 'TASKNUM':
        $sql_select="SELECT dl.FILENAME,dd.FILEDESCRIPTION FROM document_links dl LEFT JOIN document_descriptions dd ON dl.FILENAME=dd.FILENAME WHERE TASKNUM='{$_SESSION['Ident_1']}'";
        break;
    case 'ITEMNUM':
        $sql_select="SELECT FILENAME,FILEDESCRIPTION FROM vw_documents WHERE ITEMNUM='{$_SESSION['Ident_1']}'";
        break;
}
/**
* Show the files
*/
$tpl = new smarty_mycmms();
if (is_file(PROJECT_DIR.translatePath($_REQUEST['file']))) {
    if(isset($_REQUEST['setlink'])) {
       createLink($_REQUEST['file'],DOCU);
    }
    $d=dir(PROJECT_DIR);
    $_REQUEST['file']=NULL;
} else { 
    $d=dir(PROJECT_DIR.translatePath($_REQUEST['file']));
}
for($i=0,$j=0,$files=array(),$directories=array(); $entry=$d->read(); ) {
    switch(strrchr($entry, ".")): 
        case '.': 
        case '..':                                                                
            break;
        case '':        
            $directories[$j++]=array(0 => $entry, 1 => 'folder.gif'); 
            break;
        default: {
            $files[$i++] = array(
                0 => $entry,
                1 => getFileType($entry),
                2 => DBC::fetchcolumn("SELECT DISTINCT FILEDESCRIPTION FROM vw_documents WHERE FILENAME LIKE '%/".mysql_escape_string($entry)."'",0)
            );
            break;
        } // EO default
        endswitch;
    } // EO for
$d->close();
$tpl->assign('stylesheet',STYLE_PATH."/".CSS_UPLOAD);
$tpl->assign('index',$_SERVER['SCRIPT_NAME']);
$tpl->assign('object',$_SESSION['Ident_1']);
$tpl->assign('path', translatePath($_REQUEST['file']).'/');
$tpl->assign('files', $files);
$tpl->assign('directories', $directories);
$DB=DBC::get();
$result=$DB->query($sql_select);
$taskdocs=$result->fetchAll(PDO::FETCH_ASSOC);
$tpl->assign('rootdir',PROJECT_DIR);
$tpl->assign('taskdocs',$taskdocs);
$tpl->display_error('upload.tpl');
?>
