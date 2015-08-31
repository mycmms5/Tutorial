<?php
/** 
* @author  Werner Huysmans
* @access  public
* @package framework
* @subpackage action_redirecting
* @filesource
* CVS
* $Id: _redirect.php,v 1.2 2013/12/25 08:48:56 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/_redirect.php,v $
* $Log: _redirect.php,v $
* Revision 1.2  2013/12/25 08:48:56  werner
* syncing with myCMMS
*/
header("location: ../_main/list.php?query_name=CIE_LEDGER&YEAR=".$_REQUEST['YEAR']."&GL=".$_REQUEST['GL']);
?>
