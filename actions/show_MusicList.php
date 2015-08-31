<?php
require("../includes/config_mycmms.inc.php");
require("setup.php");
$DB=DBC::get();

switch ($_REQUEST['STEP']) {
case "1": { 
        if ($_REQUEST['Artist']=="on") {
            $result=$DB->query("SELECT Artist,Title,RecordingID AS 'ID',Classification AS 'HAMA' FROM records WHERE Artist LIKE '%{$_REQUEST['artist_name']}%'");
        } else {
            $result=$DB->query("SELECT Artist,Title,RecordingID AS 'ID',Classification AS 'HAMA' FROM records WHERE Category='{$_REQUEST['music_style']}'");
        }
        $records=$result->fetchAll(PDO::FETCH_ASSOC);
                
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign("path_music",DOC_LINK."music");
        $tpl->assign('records',$records);
        $tpl->display("show_MusicList_list.tpl");
        break;
    }
    default: {
        $tpl=new smarty_mycmms();
        $tpl->debugging=false;
        $tpl->caching=false;
        $tpl->assign('stylesheet',STYLE_PATH."/".CSS_SMARTY);
        $tpl->assign('wos',$records);
        $tpl->display("show_MusicList_form.tpl");
        break;
    }
}
?>   