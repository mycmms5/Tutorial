<?php
/** 
* Redirecting to Action
* 
* @author  Werner Huysmans
* @access  public
* @package framework
* @filesource
* CVS
* $Id: _redirect.php,v 1.2 2013/12/25 08:48:56 werner Exp $
* $Source: /var/www/cvs/homenet/homenet/actions/_redirect.php,v $
* $Log: _redirect.php,v $
* Revision 1.2  2013/12/25 08:48:56  werner
* syncing with myCMMS
*
* Revision 1.2  2013/04/17 05:40:08  werner
* TEMP
*
*/ 
?>
<script type="text/javascript">
    parent.title.document.getElementById('title').innerHTML='<?PHP echo $_REQUEST['title']; ?>';
    top.maintmain.location='<?PHP echo "../actions/".$_REQUEST['action']; ?>';
</script>