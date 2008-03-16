<?php

$wpconfig = realpath("../../../../wp-config.php");
if (!file_exists($wpconfig)) die; 
require_once($wpconfig);

function get_out_now() { exit; }
add_action('shutdown', 'get_out_now', -1);

//check cookies
auth_redirect();

//check for correct capability
if ( !current_user_can('edit_posts') ) 
	die;

global $wpdb;
$wpdb->table_med2play = $wpdb->prefix . 'wordtube_med2play';

$fieldname = $_POST['id'];
$content = (int) $_POST['value'];

$seg = explode('_', $fieldname);
if ($seg[0]=='p' && $seg[1]!='' && $seg[2]!='') {
          
	$playlist_id = (int) $seg[1];
	$media_id = (int) $seg[2];

	$update = "UPDATE ".$wpdb->table_med2play." SET porder = '$content' WHERE media_id = '$media_id' AND playlist_id = '$playlist_id' ";
	$wpdb->query($update);
		
}
	
print $_POST['value'];
	
?>       