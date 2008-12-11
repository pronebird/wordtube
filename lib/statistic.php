<?php
// look up for the path
require_once( dirname(__FILE__) . '/../wordtube-config.php');

global $wpdb;

if ( isset($_POST['file']) )  {
	//update the counter for this file +1
	$filename = attribute_escape($_POST['file']);
	$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->wordtube} SET counter = counter + 1 WHERE file = %s", $filename ) );
	die('1');
}
die('0');
?>