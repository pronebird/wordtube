<?php

/*
+----------------------------------------------------------------+
+	wordtube-statistics V1.60
+	by Alex Rabe reviewed by Alakhnor
+	required for wordtube
+----------------------------------------------------------------+
*/

extract($_POST, EXTR_PREFIX_SAME, "post_");

//get the filename & state again, extract seems to be not working sometimes
$vid_id = (int) $_POST['id'];
$vid_state = trim($_POST['state']);

$wpconfig = realpath("../../../wp-config.php");

if (!file_exists($wpconfig)) die; // stop when wp-config is not there

require_once($wpconfig);

// write_to_txt(print_r ($_POST, true));
// write_to_txt("$title (id $id): $state ($duration sec.)");

/*
function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);
*/

global $wpdb;
$wpdb->wordtube	= $wpdb->prefix . 'wordtube';

$wordtube_options = get_option('wordtube_options');

$select = "SELECT * FROM ".$wpdb->wordtube." WHERE vid = '".$vid_id."'";
write_to_txt($select);

$result = $wpdb->get_row($select);
write_to_txt(print_r ($result, true));

if ($result) {
	if ($wordtube_options['countcomplete']) {

		write_to_txt("countcomplete: ".$vid_state);
		// check if file completed
		if ($vid_state == "stop") {

			$counter = $result->counter + 1;
			$act_vid = $result->vid;
			$select = 'UPDATE '.$wpdb->wordtube." SET counter = '".$counter."' WHERE vid = '".$act_vid."'";
			$result = $wpdb->query($select);

			$somecontent = "$result->name (id $act_vid): $counter ($duration sec.)";
			write_to_txt("countcomplete: ".$somecontent);
		}
		
	} else {

		write_to_txt("start: ".$vid_state);

		// check if file started
		if ($vid_state == "start" || $vid_state == "play") {

			$counter = $result->counter + 1;
			$act_vid = $result->vid;
			$select = 'UPDATE '.$wpdb->wordtube." SET counter = '".$counter."' WHERE vid = '".$act_vid."'";
			$result = $wpdb->query($select);

			$somecontent = "$result->name (id $act_vid): $counter ($duration sec.)";
			write_to_txt("start: ".$somecontent);
		} else {

			write_to_txt("null");
		
		}
	}
} else {
	write_to_txt("full null");
}


// Debug function
function write_to_txt($somecontent) {

// return;
	
	$filename = ABSPATH.'statistics.txt';
	$somecontent .= "\n";

	// Let's make sure the file exists and is writable first.
	if (is_writable($filename)) {

		// In our example we're opening $filename in append mode.
		// The file pointer is at the bottom of the file hence 
		// that's where $somecontent will go when we fwrite() it.
		if (!$handle = fopen($filename, 'a')) {
			echo "Cannot open file ($filename)";
			exit;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $somecontent) === FALSE) {
			echo "Cannot write to file ($filename)";
			exit;
		}
   
		echo "Success, wrote ($somecontent) to file ($filename)";
   
		fclose($handle);

	} else {
		echo "The file $filename is not writable";
	}
}


?>