<?PHP
/**  
* Authorisation Production: Check if the logged user is allowed to use this program 
* - Check if the user is known in the database, in Production we use a simple login system. 
* - (In some cases the authorisation is abandonned)
* - Save user relevant data in the Session
* - Reloads LIST 
* 
* @author  Werner Huysmans <werner.huysmans@skynet.be>
* @access  public
* @package framework
* @subpackage authorisation
* @filesource
* CVS
* $Id: auth.php,v 1.5 2013/12/25 08:30:08 werner Exp $
* $Source: /var/www/cvs/mycmms40/mycmms40/_main/auth.php,v $
* $Log: auth.php,v $
* Revision 5.0  2015/07/26  08:30:08  werner
* - One authorisation for both systems
* - Home and TD use the profile now
* @todo  add a field to sys.system to select the default tab (e.g. in td this is now work_orders), this could be done by reading out the table and adjusting the javascipt at the bottom
*/
$nosecurity_check=true;
require("../includes/config_mycmms.inc.php");
$return_url = $HTTP_REFERER;
/**
* Check the user's credential:
* If the combination uid / password exists
*/
if(!empty($_POST['uid']) && !empty($_POST['passwd']))
{	$DB=DBC::get();
    $result=$DB->query("SELECT profile AS 'profile',uname,longname,last_login,approver,locale,dept FROM sys_groups WHERE uname='{$_POST["uid"]}' AND passwd='{$_POST['passwd']}'");
    $users=$result->fetch();
	if($users) //if the login matched 1 user...
    {	$_SESSION['last_login'] = $users['last_login'];
        $_SESSION['profile']    = $users['profile'];
      	$_SESSION['user']       = $users['uname'];
        $_SESSION['username']   = $users['longname'];
        $_SESSION['db']         =DBC::$database;
        $_SESSION['locale']     =$users['locale'];
        $_SESSION['dept']       =$users['dept'];
        $_SESSION['APPROVER']   =$users['approver'];
        $_SESSION['webcal_login']="admin";
/**
* Record the login time in the database. 
*/
	    $DB->query("UPDATE sys_groups SET last_login = NOW()+0 WHERE uname LIKE '$users->uname'");
    } else {    
/**
* When the login fails, show something.
* @todo SMARTY screen to give some more information 
*/
		require("setup.php");
        $tpl=new smarty_mycmms();
        $tpl->caching=false;
        $tpl->assign("stylesheet","");
        $tpl->display_error("fw/no_login.tpl");
        exit; 
    }
} else {    
/**
* If no username or password was submitted, the session will be destroyed.
*/
    session_destroy();	
}
$system=$_SESSION['system'];
/**
* JavaScript will reload the start screen
*/
?>
<script type=text/javascript>
function reload(system)
{	switch(system) {
    case 'td':
        top.location = "../index.php?nav=work_orders"; 
        break;
    case 'home':
        top.location = "../index.php?nav=music";        
        break;
}    
} 
setTimeout("reload('<?php echo $system; ?>');", 500)
</script>

