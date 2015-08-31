<?php
$path="C:/wamp/www/common/documents_HOMENET/";
/**
* Connect to database table dl
* 
* @var mixed
*/
$link=mysql_connect('127.0.0.1','root','');
    if (!$link) {
        die('Not connected : ' . mysql_error());
    }
$db_selected = mysql_select_db('homenet');
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}
$result=mysql_query("SELECT * FROM dd WHERE md5=0");
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    $found=0; $notfound=0;
    print "<table>";
    while ($row=mysql_fetch_array($result)) {
        $filename=$path.$row['sha1']."/".$row['filename'];
        if (file_exists($filename)) {
            $found+=1;
            # echo "<tr><td></td><td>{$row['filename']} exists</td></tr>";
        } else {
            $notfound+=1;
            echo "<tr><td>{$row['sha1']}</td><td>{$row['filename']}</td><td>does not exist</td></tr>";
        }
    }
    print "</table>";
    print "<hr>";
    echo $found." documents of were found<br>";
    echo $notfound." documents couldn't be found<br>";
?> 
