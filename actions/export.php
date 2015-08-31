<?php
/** Export FRAME: 
* 1) Presents the available export possibilities (table sys_exports)
* 2) Passes control to a secundary export module containing the format
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package framework
* @filesource
* CVS
* $Id: export_default.php,v 1.1 2013/12/25 08:50:00 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/export_default.php,v $
* $Log: export_default.php,v $
* Revision 1.1  2013/12/25 08:50:00  werner
* Synced versions with myCMMS
*
* Revision 1.4  2013/08/23 08:28:25  werner
* CVS parameters
*
*/
set_time_limit(0);
require("../includes/config_mycmms.inc.php");    

switch ($_REQUEST['STEP']) {
case "1": {
    $EXPORTFILE_ACTION=$_REQUEST['EXPORTFILE_ACTION'];
    if (empty($EXPORTFILE_ACTION)) {  
        $EXPORTFILE_ACTION=$_SERVER['PHP_SELF']; 
    } else {
        // Filename and Action are combined in 1 String separated with :
        $export_file_action=split(":",$EXPORTFILE_ACTION);
    } 
/** 
* Verify if there is an action
* Check if the PHP module is available
*/
    if (empty($export_file_action[1])) {
//        $header=(_("myCMMS cannot find a corresponding action for file {$import_file_action[0]}"));
    } else {
        require($export_file_action[1]);
//        $header=(_("myCMMS is importing {$import_file_action[0]} with script {$import_file_action[1]}"));
        export_action($export_file_action[0]); 
    }
    break;
}
default: {
    $DB=DBC::get();
    $actions=array();$i=0;
    $rootdir=opendir($doc_paths['export']);
    while($entryname=readdir($rootdir))
    {   if (!is_dir($entryname)) {
            $entryname=strtoupper($entryname);
            $result=$DB->query("SELECT filename,action FROM sys_exports WHERE filename=LEFT('$entryname',LENGTH(filename))");
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
    $tpl->assign("import_path",$doc_paths['export']);
    $tpl->assign("actions",$actions);        
    $tpl->display_error("action/export.tpl");
}
}
set_time_limit(30);
?>
