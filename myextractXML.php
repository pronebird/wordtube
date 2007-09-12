<?php

/*
+----------------------------------------------------------------+
+	wordtube-XML V1.51
+	by Alex Rabe
+   	required for wordtube
+----------------------------------------------------------------+
*/

$wpconfig = realpath("../../../wp-config.php");
if (!file_exists($wpconfig)) die; // stop when wp-config is not there

require_once($wpconfig);

function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);

global $wpdb;

// get the playlist id from querystring
$playlist_id = (int) $_GET['id'];

// Show all files when 0
if (($playlist_id == 0) or (!$playlist_id)) {
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY vid ASC");

	// Create XML output
	header("content-type:text/xml;charset=utf-8");
	
	echo "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
	echo "	<title>WordTube Playlist V1.51</title>\n";
	echo "	<trackList>\n";
	
	if (is_array ($themediafiles)){
		foreach ($themediafiles as $tmp) {
			echo "		<track>\n";
			echo "			<title>".htmlspecialchars(stripslashes($tmp->name))."</title>\n";
			echo "			<identifier>".$tmp->vid."</identifier>\n";
			echo "			<creator>".htmlspecialchars(stripslashes($tmp->creator))."</creator>\n";
			echo "			<location>".$tmp->file."</location>\n";
			echo "			<image>".$tmp->image."</image>\n";
			echo "			<info>".$tmp->link."</info>\n";
			echo "		</track>\n";
		}
	}
	 
	echo "	</trackList>\n";
	echo "</playlist>\n";

}
else
// Get playlist
{
 	$playlist = $wpdb->get_row("SELECT * FROM $wpdb->wordtube_playlist WHERE pid = '$playlist_id'");
	if (!$playlist) die;
 	$mediaids = $wpdb->get_col("SELECT media_id FROM $wpdb->wordtube_med2play WHERE playlist_id = '$playlist_id' ORDER BY media_id $playlist->playlist_order");

	// Create XML output
	header("content-type:text/xml;charset=utf-8");
	
	echo "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
	echo "	<title>".$playlist->playlist_name."</title>\n";
	echo "	<trackList>\n";
	
	if (is_array ($mediaids)){
		foreach ($mediaids as $mediaid) {
		 	
		 	$tmp = $wpdb->get_row("SELECT * FROM $wpdb->wordtube WHERE vid = '$mediaid'");
			
			echo "		<track>\n";
			echo "			<title>".htmlspecialchars(stripslashes($tmp->name))."</title>\n";
			echo "			<identifier>".$tmp->vid."</identifier>\n";
			echo "			<creator>".htmlspecialchars(stripslashes($tmp->creator))."</creator>\n";
			echo "			<location>".$tmp->file."</location>\n";
			echo "			<image>".$tmp->image."</image>\n";
			echo "			<info>".$tmp->link."</info>\n";
			echo "		</track>\n";
		}
	}
	 
	echo "	</trackList>\n";
	echo "</playlist>\n";	
}

?>