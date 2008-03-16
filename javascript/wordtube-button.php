<?php

/**
 * @author Alex Rabe
 * @copyright 2008
 * @title Old WordPress 2.3 Button integration 
 */

/******************************************************************
// Load the Script for the Button
******************************************************************/
function insert_wordtube_script() {	
?>	
	<script type='text/javascript'> 
		function wpt_buttonscript()	{ 
		if(window.tinyMCE) {

			var template = new Array();
	
			template['file'] = '<?php echo WORDTUBE_URLPATH; ?>javascript/wordtube-tinymce.php';
			template['width'] = 360;
			template['height'] = 210;
	
			args = {
				resizable : 'no',
				scrollbars : 'no',
				inline : 'yes'
			};
	
			tinyMCE.openWindow(template, args);
			return true;
		} 
	} 
	</script>
<?php
}
/******************************************************************
******************************************************************/
function wpt_addbuttons() {
 
	global $wp_db_version;

	// Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
 
	// If WordPress 2.1+ (or WPMU?) and using TinyMCE, we need to insert the buttons differently
	// Thanks to Viper007bond and An-archos for the pioneer work
	if ( 3664 <= $wp_db_version && 'true' == get_user_option('rich_editing') ) {
	 
		// add the button for wp21 in a new way
		add_filter("mce_plugins", "wptube_button_plugin", 5);
		add_filter('mce_buttons', 'wptube_button', 5);
		add_action('tinymce_before_init','wptube_button_script');
	}
	
	// Disable function when Tiny is not active
//	else {
//	 	$button_image_url = WORDTUBE_URLPATH . '/javascript/wordtube.gif';
//		buttonsnap_separator();
//		buttonsnap_jsbutton($button_image_url, __('Insert Video', 'wpTube'), 'wpt_buttonscript();');
//	}
}

// ButtonSnap needs to be loaded outside the class in order to work right
require(WORDTUBE_ABSPATH . 'javascript/wordtube-buttonsnap.php');
/******************************************************************
// used to insert button in wordpress 2.1x editor
******************************************************************/
function wptube_button($buttons) {

	array_push($buttons, "separator", "wordTube");
	return $buttons;

}
/******************************************************************
// Tell TinyMCE that there is a plugin (wp2.1)
******************************************************************/
function wptube_button_plugin($plugins) {    

	array_push($plugins, "-wordTube");    
	return $plugins;
}
/******************************************************************
// Load the TinyMCE plugin : editor_plugin.js (wp2.1)
******************************************************************/
function wptube_button_script() {	
 
 	$pluginURL =  WORDTUBE_URLPATH.'javascript/';
	echo 'tinyMCE.loadPlugin("wordTube", "'.$pluginURL.'");' . "\n"; 
	return;
}

// init process for button control
add_action('init', 'wpt_addbuttons');
add_action('edit_page_form', 'insert_wordtube_script');
add_action('edit_form_advanced', 'insert_wordtube_script');

?>