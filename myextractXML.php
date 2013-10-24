<?php
/*
+----------------------------------------------------------------+
+	wordtube media RSS output for playlist
+	by Alex Rabe reviewed by Alakhnor
+	Media RSS support by Andrej Mihajlov
+	required for wordTube
+----------------------------------------------------------------+
*/

// look up for the path
if ( !defined('ABSPATH') ) 
    require_once( dirname(__FILE__) . '/wordtube-config.php');

global $wpdb;

// get the path url from querystring
$playlist_id = $_GET['id'];

$title = 'WordTube Playlist';

$themediafiles = array();
$limit = '';

if (substr($playlist_id,0,4) == 'last') {
	$l= (int) substr($playlist_id,4);
	if ($l <= 0) $l = 10;
	$limit = ' LIMIT 0,'.$l;
	$playlist_id = '0';
}

// Otherwise gets most viewed
if ($playlist_id == 'most') {
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE counter >0 ORDER BY counter DESC LIMIT 10");
// Otherwise gets mp3
} elseif ($playlist_id == 'music') {
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE file LIKE '%.mp3%' ORDER BY vid DESC");
// Otherwise gets flv
} elseif ($playlist_id == 'video') {
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube WHERE file LIKE '%.flv%' ORDER BY vid DESC");
// Shows all files when 0
} elseif ($playlist_id == '0') {
	$themediafiles = $wpdb->get_results("SELECT * FROM $wpdb->wordtube ORDER BY vid DESC $limit");
// Otherwise gets playlist
} else {
	// Remove all evil code
	$playlist_id = intval($_GET['id']);
 	$playlist = $wpdb->get_row("SELECT * FROM $wpdb->wordtube_playlist WHERE pid = '$playlist_id'");
 	if ($playlist) {
		$select  = " SELECT * FROM {$wpdb->wordtube} w";
		$select .= " INNER JOIN {$wpdb->wordtube_med2play} m";
		$select .= " WHERE (m.playlist_id = '$playlist_id'" ;
		$select .= " AND m.media_id = w.vid) GROUP BY w.vid ";
		$select .= " ORDER BY m.porder ".$playlist->playlist_order." ,w.vid ".$playlist->playlist_order;
		$themediafiles = $wpdb->get_results( $wpdb->prepare( $select ) );
	 	$title = $playlist->playlist_name;
	}
}

// Create Media RSS output
header("content-type:text/xml;charset=utf-8");

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
echo '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
echo "\t<channel>";
echo "\n\t\t".'<title>' . esc_attr($title) . '</title>';
	
if (is_array ($themediafiles)) {

	foreach ($themediafiles as $media) {
		
        $creator = esc_attr(stripslashes($media->creator));
        if ($creator == '') 
			$creator = 'Unknown';
        
        if ($media->image == '') 
			$image = get_option('siteurl') . '/wp-content/plugins/' . dirname( plugin_basename(__FILE__) ) . '/images/wordtube.jpg';
		else 
			$image = $media->image;

		$file = pathinfo($media->file);

		echo "\n\t\t\t".'<item>';
		echo "\n\t\t\t\t".'<title>' . esc_attr($media->name) . '</title>';
		echo "\n\t\t\t\t".'<link>' . esc_attr($media->link) . '</link>';
		echo "\n\t\t\t\t".'<media:description>' . esc_attr($media->description) . '</media:description>';
		echo "\n\t\t\t\t".'<media:content url="' . esc_attr($media->file) . '" />';
		echo "\n\t\t\t\t".'<media:credit role="author">' . esc_attr($creator) . '</media:credit>';
		echo "\n\t\t\t\t".'<media:thumbnail url="' . esc_attr($image) . '" />';
		//echo "\n\t\t\t\t".'<media:id>' . $media->vid . '</media:id>';
		//echo "\n\t\t\t\t".'<media:counter>' . $media->counter . '</media:counter>';
		echo "\n\t\t\t".'</item>';
	}
}

echo "\n\t</channel>";
echo "\n</rss>";

?>