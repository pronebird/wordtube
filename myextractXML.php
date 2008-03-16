<?php


/*
+----------------------------------------------------------------+
+	wordtube-XML
+	by Alex Rabe reviewed by Alakhnor
+	required for wordTube
+----------------------------------------------------------------+
*/

$wpconfig = realpath("../../../wp-config.php");
// stop when wp-config is not there
if (!file_exists($wpconfig)) die; 
require_once($wpconfig);

// get the path url from querystring
$playlist_id = $_GET['id'];

function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);

global $wpdb;

$title = 'WordTube Playlist';
$themediafiles = array();
if (substr($playlist_id,0,4) == 'last') {
	$l= (int) substr($playlist_id,4);
	if ($l <= 0) $l = 10;
	$limit = ' LIMIT 0,'.$l;
	$playlist_id = '0';
}

// Otherwise gets most viewed
if ($playlist_id == 'most')
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE counter >0 ORDER BY counter DESC LIMIT 10");
// Otherwise gets mp3
elseif ($playlist_id == 'music')
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE file LIKE '%.mp3%' ORDER BY vid DESC");
// Otherwise gets flv
elseif ($playlist_id == 'video')
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE file LIKE '%.flv%' ORDER BY vid DESC");
// Shows all files when 0
elseif ($playlist_id == '0')
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY vid DESC ".$limit);
// Otherwise gets playlist
else {
	// Remove all evil code
	$playlist_id = intval($_GET['id']);
 	$playlist = $wpdb->get_row("SELECT * FROM $wpdb->wordtube_playlist WHERE pid = '$playlist_id'");
 	if ($playlist) {
		$select = " SELECT * FROM {$wpdb->wordtube} w
				INNER JOIN {$wpdb->wordtube_med2play} m
				WHERE (m.playlist_id = '".$playlist_id."' AND m.media_id = w.vid)
				GROUP BY w.vid 
				ORDER BY m.porder ".$playlist->playlist_order.",w.vid ".$playlist->playlist_order;
		$themediafiles = $wpdb->get_results($select);
	 	$title = $playlist->playlist_name;
	}
}

// Create XML / XSPF output
header("content-type:text/xml;charset=utf-8");
	
echo "\n"."<playlist version='1' xmlns='http://xspf.org/ns/0/'>";
echo "\n\t".'<title>'.htmlspecialchars($title).'</title>';
echo "\n\t".'<trackList>';
	
if (is_array ($themediafiles)){

	foreach ($themediafiles as $tmp) {
		
                $creator = htmlspecialchars(stripslashes($tmp->creator));
                if ($creator == '') 
					$creator = 'Unknown';
                if ($tmp->image == '') 
					$image = get_option('siteurl').'/wp-content/plugins/' . dirname(plugin_basename(__FILE__)).'/wordtube.jpg'; 
				else 
					$image = $tmp->image;
  				$file = pathinfo($tmp->file);

		echo "\n\t\t".'<track>';
		echo "\n\t\t\t".'<title>'.htmlspecialchars(stripslashes($tmp->name)).'</title>';
		echo "\n\t\t\t".'<creator>'.$creator.'</creator>';
		echo "\n\t\t\t".'<location>'.htmlspecialchars($tmp->file).'</location>';
		echo "\n\t\t\t".'<image>'.htmlspecialchars($image).'</image>';
		echo "\n\t\t\t".'<id>'.$tmp->vid.'</id>';
		echo "\n\t\t\t".'<counter>'.$tmp->counter.'</counter>';
		echo "\n\t\t\t".'<info>'.htmlspecialchars($tmp->link).'</info>';
		echo "\n\t\t".'</track>';
	}
}
	 
echo "\n\t".'</trackList>';
echo "\n"."</playlist>\n";	

?>