<?php
/**
  Copyright (C) 2003 Robert A. Wallis http://codetriangle.com

  This software is provided 'as-is', without any express or implied
  warranty.  In no event will the authors be held liable for any damages
  arising from the use of this software.

  Permission is granted to anyone to use this software for any purpose,
  including commercial applications, and to alter it and redistribute it
  freely, subject to the following restrictions:

  1. The origin of this software must not be misrepresented; you must not
     claim that you wrote the original software. If you use this software
     in a product, an acknowledgment in the product documentation would be
     appreciated but is not required.
  2. Altered source versions must be plainly marked as such, and must not be
     misrepresented as being the original software.
  3. This notice may not be removed or altered from any source distribution.
*/
/**
* Import iTUNES
*  
* @access  public
* @package tools
* @subpackage itunes
*/
function import_action($filename) {
    global $doc_paths;
    $DB=DBC::get();
    $importfile=$doc_paths['import'].$filename;
    include "itunes_xml_parser.php";
/* Copy of iTunes */
    $songs = iTunesXmlParser($importfile);
    $importFrom=1;
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>iTunes Library</title>
<style>
th {
    color: white;
    background-color: darkblue;
}
td.ALBUM {
    color: darkblue;
    font-size: 14px;
}
td.TRACK {
    font-size: 12px;
}
</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><th>artist</th><th>album artist</th><th>album title</th><th>#<th>track</th><th>?</th></tr>
<?php
if ($songs) {
	// loop through the songs in the array and get 4 fields that I want to see
    foreach ($songs as $song) {
        if ($song["Track ID"]>=$importFrom) {
            $NEW="TRACK";
	        if (strtoupper($DB_album) != strtoupper(utf8_decode($song["Album"]))) {
                $DB_album=utf8_decode($song["Album"]);
                $DB_artist=utf8_decode($song["Artist"]);
                // DB operation
                DBC::execute("REPLACE INTO itunes (Artist,Title) VALUES (:artist,:title)",array("artist"=>$DB_artist,"title"=>$DB_album));
                $NewAlbum=DBC::fetchcolumn("SELECT LAST_INSERT_ID()",0);
                $NEW="RECORD";
            }   // EO if
            DBC::execute("INSERT INTO itunes_tracks (ID,RID,TN,Track) VALUES (:id,:rid,:tn,:track)",array("id"=>$song["Track ID"],"rid"=>$NewAlbum,"tn"=>$song["Track Number"],"track"=>utf8_decode($song["Name"])));
            $output .= "<tr><td class='ALBUM'>".utf8_decode($song["Artist"])."</td>
                            <td class='ALBUM'>".utf8_decode($song["Album Artist"])."</td>
                            <td class='ALBUM'>".utf8_decode($song["Album"])."</td>
                            <td class='TRACK'>".$song["Track Number"]."</td>
                            <td class='TRACK'>".utf8_decode($song["Name"])."</td>
                            <td><B>".$NEW."</B></td></tr>";
        } // EO ImportFrom
    } // EO foreach
    print $output;
} // EO if
?>
</table>
</body>
</html>
<?php
}
?>