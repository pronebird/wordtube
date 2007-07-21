<?php

/*
+----------------------------------------------------------------+
+	wordtube-statistics V1.50
+	by Alex Rabe
+   required for wordtube
+----------------------------------------------------------------+
*/

extract($_POST, EXTR_PREFIX_SAME, "post_");

$wpconfig = realpath("../../../wp-config.php");

if (!file_exists($wpconfig)) die; // stop when wp-config is not there

require_once($wpconfig);
/*
function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);
*/
global $wpdb;

$wordtube_options=get_option('wordtube_options');

$result = $wpdb->get_row("SELECT * FROM $wpdb->wordtube WHERE file = '$file' ");

if ($result) {
	if ($wordtube_options[countcomplete]) {
		if (($state == "complete") || ($state == "stop"))  // check if file completed
		{
			$counter = $result->counter + 1;
			$act_vid = $result->vid;
			$result = $wpdb->query("UPDATE $wpdb->wordtube SET counter = '$counter' WHERE vid = '$act_vid' ");
		} 
	} else {
		if (($state == "play") || ($state == "start")) // check if file started
		{
			$counter = $result->counter + 1;
			$act_vid = $result->vid;
			$result = $wpdb->query("UPDATE $wpdb->wordtube SET counter = '$counter' WHERE vid = '$act_vid' ");
		}	
	}
}
?>