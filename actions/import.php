<?php
/** 
* Import FRAMEWORK file: 
* 1) Presents the available export possibilities (table sys_exports)
* 2) Passes control to a secundary export module containing the format
* CHANGE (WHU) on 20110516: Simplification of filename:action mechanism.
* SQL change filename=LEFT(imported_filename,LENGTH(filename)
* Action is linked on all filenames beginning with sys_imports.filename. 
* This allows the use of timestamps in the filename MONITORING  MONITORING_20110516-0835.import
* 
* @author  Werner Huysmans
* @access  public
* @package framework
* @filesource
* CVS
* $Id: import_default.php,v 1.2 2013/12/25 08:52:41 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/import_default.php,v $
* $Log: import_default.php,v $
* Revision 1.2  2013/12/25 08:52:41  werner
* Synced versions with myCMMS
*
* Revision 1.2  2013/04/17 05:44:53  werner
* Inserted CVS variables Id,Source and Log
*

*/
set_time_limit(0);
require("../includes/config_mycmms.inc.php");    

switch ($_REQUEST['STEP']) {
case "1": {
    $IMPORTFILE_ACTION=$_REQUEST['IMPORTFILE_ACTION'];
    if (empty($IMPORTFILE_ACTION)) {  
        $IMPORTFILE_ACTION=$_SERVER['PHP_SELF']; 
    } else {
        // Filename and Action are combined in 1 String separated with :
        $import_file_action=split(":",$IMPORTFILE_ACTION);
    } 
/** 
* Verify if there is an action
* Check if the PHP module is available
*/
    if (empty($import_file_action[1])) {
//        $header=(_("myCMMS cannot find a corresponding action for file {$import_file_action[0]}"));
    } else {
        require($import_file_action[1]);
//        $header=(_("myCMMS is importing {$import_file_action[0]} with script {$import_file_action[1]}"));
        import_action($import_file_action[0]); 
    }
    break;
}
default: {
    $DB=DBC::get();
    $actions=array();$i=0;
    $rootdir=opendir($doc_paths['import']);
    while($entryname=readdir($rootdir))
    {   if (!is_dir($entryname)) {
            $entryname=strtoupper($entryname);
            $result=$DB->query("SELECT filename,action FROM sys_imports WHERE filename=LEFT('$entryname',LENGTH(filename))");
            if ($result) {
                $file_action=$result->fetchColumn(1);
            } else {
                $file_action = 'default.php';
            }
            $actions[$i]["FILENAME"]=$entryname;
            $actions[$i]["ACTION"]=$file_action;
            $i++;
            }
    } // while
    closedir($rootdir);
            
    require("setup.php");
    $tpl=new smarty_mycmms();
    $tpl->caching=false;
    $tpl->debugging=false;
    $tpl->assign("stylesheet",STYLE_PATH."/".CSS_SMARTY);
    $tpl->assign("import_path",$doc_paths['import']);
    $tpl->assign("actions",$actions);        
    $tpl->display_error("action/import.tpl");
}
}
set_time_limit(30);
?>
