<?php

/*
+----------------------------------------------------------------+
+	wordtube-statistics V1.51
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

//get the filename & state again, extract seems to be not working sometimes
$file  = $_POST['file'];
$id = $_POST['id'];
$state = $_POST['state'];

$wordtube_options=get_option('wordtube_options');

$result = $wpdb->get_row("SELECT * FROM $wpdb->wordtube WHERE vid = '$id' ");

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

/* DEBUG
$filename = 'statistics.txt';
$somecontent .= $_POST['id']. " :". $_POST['title']. "(file ".$_POST['file']."): ".$_POST['state']." (".$_POST['duration']." sec.) \n";

if (is_writable($filename)) {
   if (!$handle = fopen($filename, 'a')) exit;
   if (fwrite($handle, $somecontent) === FALSE) exit;
   fclose($handle);
}
*/
?>